<?php
session_start();
require_once '../DB/db_connect.php';

// SECURITY LOCK: Student Only
if (!isset($_SESSION['Role']) || $_SESSION['Role'] !== 'student') {
    header("Location: ../login.html");
    exit();
}

$user_id = $_SESSION['UserID'];

// 1. Handle Quiz Submission & Auto-Grading
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_quiz') {
    $quiz_id = intval($_POST['quiz_id']);
    $course_id = intval($_POST['course_id']);
    $score = 0;
    $total_questions = 0;
    
    // Fetch correct answers directly from the database
    $q_stmt = $conn->prepare("SELECT QuestionID, CorrectOption FROM quiz_questions WHERE QuizID = ?");
    $q_stmt->bind_param("i", $quiz_id);
    $q_stmt->execute();
    $result = $q_stmt->get_result();
    
    $correct_answers = [];
    while($row = $result->fetch_assoc()) {
        $correct_answers[$row['QuestionID']] = strtoupper($row['CorrectOption']);
        $total_questions++;
    }
    
    // Check student answers
    $assessment_data = json_decode($_POST['assessment_data'] ?? '{}', true);
    
    foreach ($assessment_data as $q_key => $answer) {
        $q_id = intval(str_replace('q_', '', $q_key));
        if (isset($correct_answers[$q_id]) && strtoupper($answer) === $correct_answers[$q_id]) {
            $score++;
        }
    }
    
    // Save the grade to the database
    $insert_stmt = $conn->prepare("INSERT INTO quiz_grades (QuizID, UserID, Score, Total) VALUES (?, ?, ?, ?)");
    $insert_stmt->bind_param("iiii", $quiz_id, $user_id, $score, $total_questions);
    $insert_stmt->execute();
    
    $percentage = $total_questions > 0 ? round(($score / $total_questions) * 100) : 0;
    
    // Save to session for the immediate result popup
    $_SESSION['quiz_result'] = [
        'score' => $score,
        'total' => $total_questions,
        'percentage' => $percentage
    ];
    
    header("Location: Assessment.php");
    exit();
}

// 2. Fetch Available Quizzes for Student's Enrolled Courses
$quizzes_query = "
    SELECT q.QuizID, q.Title as QuizTitle, c.CourseID, c.Title as CourseName, c.Instructor, c.Category
    FROM quizzes q
    JOIN courses c ON q.CourseID = c.CourseID
    JOIN enrollments e ON c.CourseID = e.CourseID
    WHERE e.UserID = $user_id
";
$quizzes_res = $conn->query($quizzes_query);
$available_quizzes = [];
$quiz_ids = [];

if($quizzes_res) {
    while($row = $quizzes_res->fetch_assoc()) {
        $available_quizzes[] = $row;
        $quiz_ids[] = $row['QuizID'];
    }
}

// 3. Fetch Questions to pass to JavaScript
$questions_by_quiz = [];
if (!empty($quiz_ids)) {
    $ids_str = implode(',', $quiz_ids);
    $q_res = $conn->query("SELECT * FROM quiz_questions WHERE QuizID IN ($ids_str) ORDER BY QuestionID ASC");
    while($row = $q_res->fetch_assoc()) {
        $questions_by_quiz[$row['QuizID']][] = [
            'id' => $row['QuestionID'],
            'text' => $row['QuestionText'],
            'a' => $row['OptionA'],
            'b' => $row['OptionB'],
            'c' => $row['OptionC'],
            'd' => $row['OptionD']
        ];
    }
}

// 4. Fetch Previous Grades
$grades_query = "SELECT QuizID, MAX(Score) as BestScore, Total FROM quiz_grades WHERE UserID = $user_id GROUP BY QuizID";
$grades_res = $conn->query($grades_query);
$user_grades = [];
if($grades_res) {
    while($g = $grades_res->fetch_assoc()) {
        $user_grades[$g['QuizID']] = $g;
    }
}

$quiz_result = isset($_SESSION['quiz_result']) ? $_SESSION['quiz_result'] : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessments - AAST Extra Learning</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .assessment-container { max-width: 1200px; margin: 0 auto; padding: 40px 20px; }
        .assessment-header { margin-bottom: 40px; }
        .assessment-header h1 { font-size: 32px; font-weight: 800; color: #010d1c; margin-bottom: 10px; }
        .assessment-header p { color: #6b7280; font-size: 16px; }

        .courses-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 24px; margin-bottom: 40px; }
        .course-card { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08); transition: all 0.3s ease; }
        .course-card:hover { transform: translateY(-4px); box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12); }
        .course-image { width: 100%; height: 160px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; font-size: 40px; color: white; text-align: center; padding: 20px;}
        .course-info { padding: 20px; }
        .course-name { font-size: 18px; font-weight: 700; color: #010d1c; margin-bottom: 8px; }
        .course-instructor { font-size: 13px; color: #6b7280; margin-bottom: 12px; }

        .progress-bar { width: 100%; height: 6px; background-color: #e5e7eb; border-radius: 3px; overflow: hidden; margin-bottom: 5px; }
        .progress-fill { height: 100%; transition: width 0.3s ease; }

        .btn-take-quiz { width: 100%; padding: 10px; background-color: #4a90e2; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 14px; transition: all 0.3s ease; }
        .btn-take-quiz:hover { background-color: #357abd; }

        /* Modal Styles */
        .quiz-modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 1000; align-items: center; justify-content: center; padding: 20px; }
        .quiz-modal.active { display: flex; }
        .quiz-content { background: white; border-radius: 12px; padding: 40px; max-width: 700px; width: 100%; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3); }
        .quiz-header { margin-bottom: 30px; text-align: center; }
        .quiz-header h2 { font-size: 24px; color: #010d1c; margin-bottom: 10px; }
        
        .question-group { margin-bottom: 30px; background: #f8fafc; padding: 20px; border-radius: 8px; border-left: 4px solid #4a90e2; }
        .question-text { font-size: 16px; font-weight: 600; color: #010d1c; margin-bottom: 15px; }
        .answer-option { display: flex; align-items: center; margin-bottom: 10px; padding: 12px; border: 1px solid #e5e7eb; border-radius: 6px; cursor: pointer; transition: all 0.2s; background: white; }
        .answer-option:hover { border-color: #4a90e2; background: #f0fdf4; }
        .answer-option input[type="radio"] { margin-right: 12px; cursor: pointer; }
        .answer-option label { flex: 1; cursor: pointer; margin: 0; }

        .quiz-buttons { display: flex; gap: 12px; margin-top: 30px; justify-content: center; }
        .btn-submit { padding: 12px 30px; background-color: #4a90e2; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 14px; }
        .btn-close { padding: 12px 30px; background-color: #e5e7eb; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 14px; }

        /* Result Styles */
        .result-card { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 40px; border-radius: 12px; text-align: center; margin-bottom: 30px; }
        .result-score { font-size: 48px; font-weight: 800; margin-bottom: 10px; }
        .result-details { font-size: 16px; margin-bottom: 20px; }
        .btn-clear-result { padding: 10px 24px; background-color: white; color: #059669; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">
                <h1 class="logo-text">AASTMT</h1>
                <span class="logo-subtitle">Success Hub</span>
            </div>
            <ul class="nav-links">
                <li><a href="sdashboard.php" class="nav-link">Dashboard</a></li>
                <li><a href="my_courses.php" class="nav-link">My Courses</a></li>
                <li><a href="Assessment.php" class="nav-link active">Assessments</a></li>
                <li><a href="../logout.php" class="nav-link btn-logout" style="color: #ef4444;">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="assessment-container">
        <div class="assessment-header">
            <h1>Course Assessments</h1>
            <p>Test your knowledge with quizzes created by your instructors.</p>
        </div>

        <?php if ($quiz_result): ?>
        <div class="result-card" style="<?php echo $quiz_result['percentage'] < 50 ? 'background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);' : ''; ?>">
            <div class="result-score"><?php echo $quiz_result['percentage']; ?>%</div>
            <div class="result-details">
                You scored <?php echo $quiz_result['score']; ?> out of <?php echo $quiz_result['total']; ?> questions
            </div>
            <form method="POST">
                <button type="submit" name="clear_result" class="btn-clear-result" style="<?php echo $quiz_result['percentage'] < 50 ? 'color: #b91c1c;' : ''; ?>">Return to Quizzes</button>
            </form>
        </div>
        <?php 
            unset($_SESSION['quiz_result']);
        endif; 
        ?>

        <?php if (count($available_quizzes) > 0): ?>
        <div class="courses-grid">
            <?php foreach ($available_quizzes as $quiz): 
                $quiz_id = $quiz['QuizID'];
                $has_taken = isset($user_grades[$quiz_id]);
                $grade_percent = $has_taken ? round(($user_grades[$quiz_id]['BestScore'] / $user_grades[$quiz_id]['Total']) * 100) : 0;
            ?>
            <div class="course-card">
                <div class="course-image">
                    <?php echo htmlspecialchars($quiz['QuizTitle']); ?>
                </div>
                <div class="course-info">
                    <div class="course-name"><?php echo htmlspecialchars($quiz['CourseName']); ?></div>
                    <div class="course-instructor">Instructor: <?php echo htmlspecialchars($quiz['Instructor']); ?></div>
                    
                    <?php if ($has_taken): ?>
                        <div style="margin-bottom: 15px;">
                            <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 5px; font-weight: 600;">
                                <span style="color: #64748b;">Previous Best:</span>
                                <span style="color: <?php echo $grade_percent >= 50 ? '#10b981' : '#ef4444'; ?>"><?php echo $grade_percent; ?>%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $grade_percent; ?>%; background: <?php echo $grade_percent >= 50 ? '#10b981' : '#ef4444'; ?>"></div>
                            </div>
                        </div>
                        <button class="btn-take-quiz" style="background: #f1f5f9; color: #475569;" onclick="startQuiz(<?php echo $quiz['QuizID']; ?>, '<?php echo htmlspecialchars(addslashes($quiz['QuizTitle'])); ?>')">
                            Retake Quiz
                        </button>
                    <?php else: ?>
                        <button class="btn-take-quiz" onclick="startQuiz(<?php echo $quiz['QuizID']; ?>, '<?php echo htmlspecialchars(addslashes($quiz['QuizTitle'])); ?>')">
                            Start Assessment
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div style="text-align: center; padding: 60px 20px;">
            <h2>No Assessments Available</h2>
            <p style="color: #6b7280; margin-bottom: 20px;">There are no active quizzes for your enrolled courses yet.</p>
            <a href="my_courses.php" style="padding: 12px 30px; background-color: #4a90e2; color: white; border-radius: 6px; text-decoration: none; font-weight: 600;">Go to My Courses</a>
        </div>
        <?php endif; ?>
    </div>

    <div class="quiz-modal" id="quizModal">
        <div class="quiz-content">
            <form id="quizForm" method="POST">
                <input type="hidden" name="action" value="submit_quiz">
                <input type="hidden" name="quiz_id" id="quizIdInput">
                <input type="hidden" name="course_id" id="courseIdInput">
                <input type="hidden" name="assessment_data" id="assessmentData">
                
                <div class="quiz-header">
                    <h2 id="quizTitleDisplay">Course Assessment</h2>
                    <p style="color: #64748b; font-size: 14px;">Please select one answer for each question.</p>
                </div>

                <div id="dynamicQuestionsContainer"></div>

                <div class="quiz-buttons">
                    <button type="button" class="btn-close" onclick="closeQuiz()">Cancel</button>
                    <button type="submit" class="btn-submit">Submit Answers</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Pass PHP database questions to Javascript safely
        const quizDataDB = <?php echo json_encode($questions_by_quiz); ?>;

        function startQuiz(quizId, quizTitle) {
            document.getElementById('quizIdInput').value = quizId;
            document.getElementById('quizTitleDisplay').textContent = quizTitle;
            
            const questions = quizDataDB[quizId];
            const container = document.getElementById('dynamicQuestionsContainer');
            
            if (!questions || questions.length === 0) {
                container.innerHTML = '<p style="text-align:center; color:#ef4444; font-weight:600;">No questions have been added to this quiz yet.</p>';
                document.querySelector('.btn-submit').style.display = 'none';
            } else {
                let html = '';
                questions.forEach((q, index) => {
                    html += `
                    <div class="question-group">
                        <div class="question-text">${index + 1}. ${q.text}</div>
                        <div class="answer-option"><input type="radio" name="q_${q.id}" id="q${q.id}A" value="A" required><label for="q${q.id}A">A) ${q.a}</label></div>
                        <div class="answer-option"><input type="radio" name="q_${q.id}" id="q${q.id}B" value="B"><label for="q${q.id}B">B) ${q.b}</label></div>
                        <div class="answer-option"><input type="radio" name="q_${q.id}" id="q${q.id}C" value="C"><label for="q${q.id}C">C) ${q.c}</label></div>
                        <div class="answer-option"><input type="radio" name="q_${q.id}" id="q${q.id}D" value="D"><label for="q${q.id}D">D) ${q.d}</label></div>
                    </div>`;
                });
                container.innerHTML = html;
                document.querySelector('.btn-submit').style.display = 'block';
            }
            
            document.getElementById('quizModal').classList.add('active');
        }

        function closeQuiz() {
            document.getElementById('quizModal').classList.remove('active');
            document.getElementById('quizForm').reset();
        }

        // Handle the submission formatting
        document.getElementById('quizForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const answers = {};
            
            // Loop through form data and grab only the radio button answers
            for (let [key, value] of formData.entries()) {
                if (key.startsWith('q_')) {
                    answers[key] = value;
                }
            }
            
            // Package it as JSON for the PHP backend to decode
            document.getElementById('assessmentData').value = JSON.stringify(answers);
            this.submit();
        });
    </script>
</body>
</html>