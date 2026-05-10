<?php
session_start();
require_once '../DB/db_connect.php';

// SECURITY LOCK: Student Only
if (!isset($_SESSION['Role']) || $_SESSION['Role'] !== 'student') {
    header("Location: ../login.html");
    exit();
}

$user_id = $_SESSION['UserID'];

// FIXED: Removed the missing 'lessons' table and 'e.Progress' column from the query
$courses_query = "SELECT 
    c.CourseID,
    c.Title as CourseName,
    c.Instructor,
    c.Description
FROM enrollments e
JOIN courses c ON e.CourseID = c.CourseID
WHERE e.UserID = $user_id";

$courses_result = $conn->query($courses_query);
$courses = [];

if ($courses_result && $courses_result->num_rows > 0) {
    while ($row = $courses_result->fetch_assoc()) {
        $courses[] = $row;
    }
}

// Handle quiz attempt submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_quiz') {
    $course_id = intval($_POST['course_id']);
    $score = 0;
    $total_questions = 0;
    
    // Count questions and calculate score
    $assessment_data = json_decode($_POST['assessment_data'] ?? '{}', true);
    $total_questions = count($assessment_data);
    
    foreach ($assessment_data as $question_id => $answer) {
        // Correct answers database (can be expanded)
        $correct_answers = [
            'q1' => 'c', // Example: Question 1, answer C
            'q2' => 'a',
            'q3' => 'b',
            'q4' => 'd',
            'q5' => 'a'
        ];
        
        if (isset($correct_answers[$question_id]) && $correct_answers[$question_id] === $answer) {
            $score++;
        }
    }
    
    $percentage = $total_questions > 0 ? round(($score / $total_questions) * 100) : 0;
    
    $_SESSION['quiz_result'] = [
        'course_id' => $course_id,
        'score' => $score,
        'total' => $total_questions,
        'percentage' => $percentage,
        'timestamp' => date('Y-m-d H:i:s')
    ];
}

$quiz_result = isset($_SESSION['quiz_result']) ? $_SESSION['quiz_result'] : null;
$selected_course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessments - AAST Extra Learning</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .assessment-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .assessment-header {
            margin-bottom: 40px;
        }

        .assessment-header h1 {
            font-size: 32px;
            font-weight: 800;
            color: #010d1c;
            margin-bottom: 10px;
        }

        .assessment-header p {
            color: #6b7280;
            font-size: 16px;
        }

        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }

        .course-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .course-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
        }

        .course-image {
            width: 100%;
            height: 160px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            color: white;
        }

        .course-info {
            padding: 20px;
        }

        .course-name {
            font-size: 18px;
            font-weight: 700;
            color: #010d1c;
            margin-bottom: 8px;
        }

        .course-instructor {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 12px;
        }

        .course-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 13px;
        }

        .course-meta span {
            color: #6b7280;
        }

        .progress-bar {
            width: 100%;
            height: 6px;
            background-color: #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
            margin-bottom: 12px;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #4a90e2 0%, #357abd 100%);
            transition: width 0.3s ease;
        }

        .btn-take-quiz {
            width: 100%;
            padding: 10px;
            background-color: #4a90e2;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .btn-take-quiz:hover {
            background-color: #357abd;
        }

        .quiz-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .quiz-modal.active {
            display: flex;
        }

        .quiz-content {
            background: white;
            border-radius: 12px;
            padding: 40px;
            max-width: 700px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .quiz-header {
            margin-bottom: 30px;
            text-align: center;
        }

        .quiz-header h2 {
            font-size: 24px;
            color: #010d1c;
            margin-bottom: 10px;
        }

        .quiz-header p {
            color: #6b7280;
        }

        .question-group {
            margin-bottom: 30px;
        }

        .question-text {
            font-size: 16px;
            font-weight: 600;
            color: #010d1c;
            margin-bottom: 12px;
        }

        .answer-option {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .answer-option:hover {
            border-color: #4a90e2;
            background: #f3f7ff;
        }

        .answer-option input[type="radio"] {
            margin-right: 12px;
            cursor: pointer;
        }

        .answer-option input[type="radio"]:checked + label {
            font-weight: 600;
            color: #4a90e2;
        }

        .answer-option label {
            flex: 1;
            cursor: pointer;
            margin: 0;
        }

        .quiz-buttons {
            display: flex;
            gap: 12px;
            margin-top: 30px;
            justify-content: center;
        }

        .btn-submit {
            padding: 12px 30px;
            background-color: #4a90e2;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            background-color: #357abd;
        }

        .btn-close {
            padding: 12px 30px;
            background-color: #e5e7eb;
            color: #010d1c;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .btn-close:hover {
            background-color: #d1d5db;
        }

        .result-card {
            background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
            color: white;
            padding: 40px;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 30px;
        }

        .result-score {
            font-size: 48px;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .result-details {
            font-size: 16px;
            opacity: 0.9;
            margin-bottom: 20px;
        }

        .result-feedback {
            font-size: 18px;
            font-weight: 600;
            padding: 12px 24px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 6px;
            display: inline-block;
        }

        .btn-clear-result {
            margin-top: 20px;
            padding: 10px 24px;
            background-color: white;
            color: #4a90e2;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-clear-result:hover {
            background-color: #f3f7ff;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state h2 {
            font-size: 24px;
            color: #010d1c;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #6b7280;
            margin-bottom: 20px;
        }

        .btn-browse {
            padding: 12px 30px;
            background-color: #4a90e2;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .btn-browse:hover {
            background-color: #357abd;
        }
        
        /* --- New Footer Styling --- */
        .footer {
            background-color: white;
            border-top: 1px solid #e5e7eb;
            padding: 24px 20px;
            margin-top: 60px;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
        }

        @media (min-width: 768px) {
            .footer-content {
                flex-direction: row;
                justify-content: space-between;
            }
        }

        .footer p {
            color: #6b7280;
            font-size: 14px;
            margin: 0;
        }

        .footer-links {
            display: flex;
            gap: 24px;
        }

        .footer-links a {
            color: #6b7280;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .footer-links a:hover {
            color: #4a90e2;
        }

        @media (max-width: 768px) {
            .assessment-container {
                padding: 20px 15px;
            }

            .courses-grid {
                grid-template-columns: 1fr;
            }

            .quiz-content {
                padding: 20px;
            }

            .assessment-header h1 {
                font-size: 24px;
            }
        }
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
                <li><a href="../logout.php" class="nav-link btn-logout">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="assessment-container">
        <div class="assessment-header">
            <h1>Course Assessments</h1>
            <p>Test your knowledge with quizzes for your enrolled courses</p>
        </div>

        <?php if ($quiz_result): ?>
        <div class="result-card">
            <div class="result-score"><?php echo $quiz_result['percentage']; ?>%</div>
            <div class="result-details">
                You scored <?php echo $quiz_result['score']; ?> out of <?php echo $quiz_result['total']; ?> questions
            </div>
            <div class="result-feedback">
                <?php 
                    if ($quiz_result['percentage'] >= 80) {
                        echo "✨ Excellent! Great job!";
                    } elseif ($quiz_result['percentage'] >= 60) {
                        echo "👏 Good! You're doing well!";
                    } else {
                        echo "💪 Keep studying and try again!";
                    }
                ?>
            </div>
            <form method="POST" style="margin-top: 20px;">
                <button type="submit" name="clear_result" class="btn-clear-result">Take Another Quiz</button>
            </form>
        </div>
        <?php 
            unset($_SESSION['quiz_result']);
        endif; 
        ?>

        <?php if (count($courses) > 0): ?>
        <div class="courses-grid">
            <?php foreach ($courses as $course): ?>
            <div class="course-card">
                <div class="course-image">
                    <?php echo substr(htmlspecialchars($course['CourseName']), 0, 1); ?>
                </div>
                <div class="course-info">
                    <div class="course-name"><?php echo htmlspecialchars($course['CourseName']); ?></div>
                    <div class="course-instructor"><?php echo htmlspecialchars($course['Instructor']); ?></div>
                    
                    <div class="course-meta">
                        <span>1 Assessment</span>
                        <span><?php echo htmlspecialchars($course['Category'] ?? 'General'); ?></span>
                    </div>
                    
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: 0%;"></div>
                    </div>
                    
                    <button class="btn-take-quiz" onclick="startQuiz(<?php echo $course['CourseID']; ?>, '<?php echo htmlspecialchars(addslashes($course['CourseName'])); ?>')">
                        Take Quiz
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <h2>No Courses Yet</h2>
            <p>Enroll in a course to take assessments and test your knowledge</p>
            <a href="../Courses.php" class="btn-browse">Browse Courses</a>
        </div>
        <?php endif; ?>
    </div>

    <div class="quiz-modal" id="quizModal">
        <div class="quiz-content">
            <form id="quizForm" method="POST">
                <input type="hidden" name="action" value="submit_quiz">
                <input type="hidden" name="course_id" id="courseId">
                <input type="hidden" name="assessment_data" id="assessmentData">
                
                <div class="quiz-header">
                    <h2 id="quizTitle">Course Assessment</h2>
                    <p>Answer all questions to complete the assessment</p>
                </div>

                <div class="question-group">
                    <div class="question-text">1. What is the primary goal of continuous learning?</div>
                    <div class="answer-option">
                        <input type="radio" name="q1" id="q1a" value="a">
                        <label for="q1a">To memorize facts</label>
                    </div>
                    <div class="answer-option">
                        <input type="radio" name="q1" id="q1b" value="b">
                        <label for="q1b">To stay competitive</label>
                    </div>
                    <div class="answer-option">
                        <input type="radio" name="q1" id="q1c" value="c" checked>
                        <label for="q1c">To improve skills and adaptability</label>
                    </div>
                    <div class="answer-option">
                        <input type="radio" name="q1" id="q1d" value="d">
                        <label for="q1d">None of the above</label>
                    </div>
                </div>

                <div class="question-group">
                    <div class="question-text">2. Which is the most effective learning method?</div>
                    <div class="answer-option">
                        <input type="radio" name="q2" id="q2a" value="a" checked>
                        <label for="q2a">Active practice and application</label>
                    </div>
                    <div class="answer-option">
                        <input type="radio" name="q2" id="q2b" value="b">
                        <label for="q2b">Passive reading</label>
                    </div>
                    <div class="answer-option">
                        <input type="radio" name="q2" id="q2c" value="c">
                        <label for="q2c">Memorization</label>
                    </div>
                    <div class="answer-option">
                        <input type="radio" name="q2" id="q2d" value="d">
                        <label for="q2d">Lectures only</label>
                    </div>
                </div>

                <div class="question-group">
                    <div class="question-text">3. How often should you practice to master a skill?</div>
                    <div class="answer-option">
                        <input type="radio" name="q3" id="q3a" value="a">
                        <label for="q3a">Once a month</label>
                    </div>
                    <div class="answer-option">
                        <input type="radio" name="q3" id="q3b" value="b" checked>
                        <label for="q3b">Regularly and consistently</label>
                    </div>
                    <div class="answer-option">
                        <input type="radio" name="q3" id="q3c" value="c">
                        <label for="q3c">Once a year</label>
                    </div>
                    <div class="answer-option">
                        <input type="radio" name="q3" id="q3d" value="d">
                        <label for="q3d">Never</label>
                    </div>
                </div>

                <div class="question-group">
                    <div class="question-text">4. What does AASTMT stand for?</div>
                    <div class="answer-option">
                        <input type="radio" name="q4" id="q4a" value="a">
                        <label for="q4a">Academy of Arts and Sports Technology</label>
                    </div>
                    <div class="answer-option">
                        <input type="radio" name="q4" id="q4b" value="b">
                        <label for="q4b">Advanced Analytics and Software Training</label>
                    </div>
                    <div class="answer-option">
                        <input type="radio" name="q4" id="q4c" value="c">
                        <label for="q4c">Academy of Advanced Skills and Technology Management</label>
                    </div>
                    <div class="answer-option">
                        <input type="radio" name="q4" id="q4d" value="d" checked>
                        <label for="q4d">Alexandria Academy of Science and Technology Management</label>
                    </div>
                </div>

                <div class="question-group">
                    <div class="question-text">5. What is the best way to overcome learning challenges?</div>
                    <div class="answer-option">
                        <input type="radio" name="q5" id="q5a" value="a" checked>
                        <label for="q5a">Seek help and persist</label>
                    </div>
                    <div class="answer-option">
                        <input type="radio" name="q5" id="q5b" value="b">
                        <label for="q5b">Give up</label>
                    </div>
                    <div class="answer-option">
                        <input type="radio" name="q5" id="q5c" value="c">
                        <label for="q5c">Ignore them</label>
                    </div>
                    <div class="answer-option">
                        <input type="radio" name="q5" id="q5d" value="d">
                        <label for="q5d">Postpone indefinitely</label>
                    </div>
                </div>

                <div class="quiz-buttons">
                    <button type="submit" class="btn-submit">Submit Quiz</button>
                    <button type="button" class="btn-close" onclick="closeQuiz()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <footer class="footer">
        <div class="footer-content">
            <p>&copy; 2026 AASTMT Success Hub. All rights reserved.</p>
            <div class="footer-links">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
                <a href="#">Contact Us</a>
            </div>
        </div>
    </footer>

    <script>
        function startQuiz(courseId, courseName) {
            document.getElementById('courseId').value = courseId;
            document.getElementById('quizTitle').textContent = courseName + ' - Assessment Quiz';
            document.getElementById('quizModal').classList.add('active');
            
            // Reset form
            document.getElementById('quizForm').reset();
        }

        function closeQuiz() {
            document.getElementById('quizModal').classList.remove('active');
        }

        document.getElementById('quizForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Collect answers
            const assessment_data = {
                'q1': document.querySelector('input[name="q1"]:checked')?.value || '',
                'q2': document.querySelector('input[name="q2"]:checked')?.value || '',
                'q3': document.querySelector('input[name="q3"]:checked')?.value || '',
                'q4': document.querySelector('input[name="q4"]:checked')?.value || '',
                'q5': document.querySelector('input[name="q5"]:checked')?.value || ''
            };
            
            document.getElementById('assessmentData').value = JSON.stringify(assessment_data);
            this.submit();
        });

        // Close modal when clicking outside
        document.getElementById('quizModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeQuiz();
            }
        });
    </script>
</body>
</html>