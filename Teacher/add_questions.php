<?php
session_start();
require_once '../DB/db_connect.php';

if (!isset($_SESSION['Role']) || $_SESSION['Role'] !== 'teacher') {
    header("Location: ../login.html"); exit();
}

$quiz_id = intval($_GET['quiz_id'] ?? 0);
if ($quiz_id === 0) die("Invalid Quiz ID.");

// Fetch the Quiz Title for the header
$quiz_stmt = $conn->query("SELECT Title FROM quizzes WHERE QuizID = $quiz_id");
$quiz_title = $quiz_stmt->fetch_assoc()['Title'];

$message = "";

// Handle adding a new question
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $q_text = $conn->real_escape_string($_POST['question_text']);
    $opt_a = $conn->real_escape_string($_POST['opt_a']);
    $opt_b = $conn->real_escape_string($_POST['opt_b']);
    $opt_c = $conn->real_escape_string($_POST['opt_c']);
    $opt_d = $conn->real_escape_string($_POST['opt_d']);
    $correct = $conn->real_escape_string($_POST['correct_option']);

    $stmt = $conn->prepare("INSERT INTO quiz_questions (QuizID, QuestionText, OptionA, OptionB, OptionC, OptionD, CorrectOption) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $quiz_id, $q_text, $opt_a, $opt_b, $opt_c, $opt_d, $correct);
    
    if ($stmt->execute()) {
        $message = "Question added successfully!";
    }
}

// Fetch existing questions for this quiz to display them below
$questions = $conn->query("SELECT * FROM quiz_questions WHERE QuizID = $quiz_id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Questions | Success Hub</title>
    <link rel="stylesheet" href="tDashboard.css">
    <style>
        .form-card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 30px; max-width: 800px; }
        textarea, input, select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; margin-bottom: 15px; font-family: sans-serif; }
        .grid-options { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .btn-save { background: #10b981; color: white; padding: 12px 25px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; width: 100%; }
        .q-box { background: #f8fafc; padding: 20px; border-radius: 8px; border-left: 4px solid #4a90e2; margin-bottom: 15px; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <h2 style="padding: 20px; color: #4a90e2;">Teacher Portal</h2>
        <a href="tdashboard.php">Dashboard</a>
        <a href="manage_quizzes.php" class="active">📝 Manage Quizzes</a>
        <a href="../logout.php" class="logout" style="margin-top: auto;">Logout</a>
    </aside>

    <main class="content">
        <div class="page-header">
            <h1>Building: <?php echo htmlspecialchars($quiz_title); ?></h1>
            <a href="manage_quizzes.php" style="color: #4a90e2; text-decoration: none;">← Back to Quizzes</a>
        </div>

        <div class="form-card">
            <h3 style="margin-bottom: 20px;">Add a Multiple Choice Question</h3>
            <?php if($message) echo "<p style='color: #10b981; font-weight: 600; margin-bottom: 15px;'>$message</p>"; ?>
            
            <form method="POST">
                <label style="font-weight: 600;">Question Text</label>
                <textarea name="question_text" rows="3" required placeholder="What is the capital of France?"></textarea>

                <div class="grid-options">
                    <div>
                        <label>Option A</label>
                        <input type="text" name="opt_a" required>
                    </div>
                    <div>
                        <label>Option B</label>
                        <input type="text" name="opt_b" required>
                    </div>
                    <div>
                        <label>Option C</label>
                        <input type="text" name="opt_c" required>
                    </div>
                    <div>
                        <label>Option D</label>
                        <input type="text" name="opt_d" required>
                    </div>
                </div>

                <label style="font-weight: 600; color: #10b981;">Which Option is Correct?</label>
                <select name="correct_option" required>
                    <option value="A">Option A</option>
                    <option value="B">Option B</option>
                    <option value="C">Option C</option>
                    <option value="D">Option D</option>
                </select>

                <button type="submit" class="btn-save">Save Question & Add Another</button>
            </form>
        </div>

        <div class="form-card">
            <h3>Questions Currently in this Quiz (<?php echo $questions->num_rows; ?>)</h3>
            <?php while($q = $questions->fetch_assoc()): ?>
                <div class="q-box">
                    <p style="font-weight: 600; margin-bottom: 10px;"><?php echo htmlspecialchars($q['QuestionText']); ?></p>
                    <ul style="list-style-type: none; padding: 0; color: #64748b; font-size: 14px;">
                        <li>A) <?php echo htmlspecialchars($q['OptionA']); ?></li>
                        <li>B) <?php echo htmlspecialchars($q['OptionB']); ?></li>
                        <li>C) <?php echo htmlspecialchars($q['OptionC']); ?></li>
                        <li>D) <?php echo htmlspecialchars($q['OptionD']); ?></li>
                    </ul>
                    <p style="margin-top: 10px; font-size: 13px; color: #10b981; font-weight: 600;">Correct Answer: <?php echo $q['CorrectOption']; ?></p>
                </div>
            <?php endwhile; ?>
        </div>
    </main>
</body>
</html>