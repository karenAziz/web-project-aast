<?php
session_start();
require_once '../DB/db_connect.php';

// SECURITY LOCK: Student Only
if (!isset($_SESSION['Role']) || $_SESSION['Role'] !== 'student') {
    header("Location: ../login.html");
    exit();
}

$user_id = $_SESSION['UserID'];
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : (isset($_POST['course_id']) ? intval($_POST['course_id']) : 0);

if ($course_id === 0) {
    die("Invalid Course ID. Please go back and select a course.");
}

// 1. Check if the user is already enrolled
$check_stmt = $conn->prepare("SELECT * FROM enrollments WHERE UserID = ? AND CourseID = ?");
$check_stmt->bind_param("ii", $user_id, $course_id);
$check_stmt->execute();
if ($check_stmt->get_result()->num_rows > 0) {
    // If already enrolled, send them straight to their courses
    header("Location: my_courses.php");
    exit();
}

// 2. Fetch the course details
$course_stmt = $conn->prepare("SELECT Title, Instructor FROM courses WHERE CourseID = ?");
$course_stmt->bind_param("i", $course_id);
$course_stmt->execute();
$course_result = $course_stmt->get_result();

if ($course_result->num_rows === 0) {
    die("Course not found.");
}
$course = $course_result->fetch_assoc();

// Simulated Price (Add a 'Price' column to your courses table later if needed)
$course_price = 500.00; 

$error_msg = '';

// 3. Handle the Payment and Enrollment Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Start a SQL Transaction (Ensures both payment AND enrollment succeed, or neither do)
    $conn->begin_transaction();

    try {
        // A. Insert into payments table
        $payment_status = 'Completed';
        $payment_date = date('Y-m-d H:i:s');
        
        $pay_stmt = $conn->prepare("INSERT INTO payments (Amount, Date, Status, UserID, CourseID) VALUES (?, ?, ?, ?, ?)");
        $pay_stmt->bind_param("dssii", $course_price, $payment_date, $payment_status, $user_id, $course_id);
        $pay_stmt->execute();

        // B. Insert into enrollments table
        $enroll_stmt = $conn->prepare("INSERT INTO enrollments (UserID, CourseID) VALUES (?, ?)");
        $enroll_stmt->bind_param("ii", $user_id, $course_id);
        $enroll_stmt->execute();

        // C. Commit the transaction to save it to the database
        $conn->commit();

        // D. Redirect to My Courses
        header("Location: my_courses.php?status=enrolled");
        exit();

    } catch (Exception $e) {
        // Rollback the transaction if an error occurs
        $conn->rollback();
        $error_msg = "Transaction failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - AAST Extra Learning</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        body { background-color: #f3f4f6; }
        .checkout-container {
            max-width: 800px;
            margin: 60px auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        
        .order-summary {
            background: linear-gradient(135deg, #010d1c 0%, #1a3a52 100%);
            color: white;
            padding: 40px;
        }

        .payment-form {
            padding: 40px;
        }

        h2 { margin-bottom: 20px; font-size: 24px; color: #010d1c; }
        .order-summary h2 { color: white; }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 600;
            color: #4b5563;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
        }

        .row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .btn-pay {
            width: 100%;
            padding: 14px;
            background-color: #4a90e2;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 10px;
            transition: 0.3s;
        }

        .btn-pay:hover { background-color: #357abd; }
        
        .price-tag {
            font-size: 36px;
            font-weight: 800;
            margin: 20px 0;
            color: #4a90e2;
        }

        .course-meta {
            margin-bottom: 10px;
            color: #d1d5db;
        }

        .error-message {
            background-color: #fee2e2;
            color: #dc2626;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        @media(max-width: 768px) {
            .checkout-container { grid-template-columns: 1fr; }
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
                <li><a href="../Courses.php" class="nav-link">Browse Courses</a></li>
                <li><a href="my_courses.php" class="nav-link">My Courses</a></li>
            </ul>
        </div>
    </nav>

    <div class="checkout-container">
        <div class="order-summary">
            <h2>Order Summary</h2>
            <div class="course-meta">Course Title:</div>
            <h3 style="font-size: 20px; margin-bottom: 20px;"><?php echo htmlspecialchars($course['Title']); ?></h3>
            
            <div class="course-meta">Instructor:</div>
            <h4 style="margin-bottom: 30px;"><?php echo htmlspecialchars($course['Instructor']); ?></h4>
            
            <hr style="border-color: rgba(255,255,255,0.1); margin-bottom: 20px;">
            
            <div class="course-meta">Total Amount:</div>
            <div class="price-tag"><?php echo number_format($course_price, 2); ?> L.E.</div>
        </div>

        <div class="payment-form">
            <h2>Payment Details</h2>
            
            <?php if($error_msg): ?>
                <div class="error-message"><?php echo $error_msg; ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                
                <div class="form-group">
                    <label>Name on Card</label>
                    <input type="text" class="form-control" placeholder="John Doe" required>
                </div>
                
                <div class="form-group">
                    <label>Card Number</label>
                    <input type="text" class="form-control" placeholder="0000 0000 0000 0000" maxlength="19" required>
                </div>
                
                <div class="row">
                    <div class="form-group">
                        <label>Expiry Date</label>
                        <input type="text" class="form-control" placeholder="MM/YY" maxlength="5" required>
                    </div>
                    <div class="form-group">
                        <label>CVV</label>
                        <input type="text" class="form-control" placeholder="123" maxlength="3" required>
                    </div>
                </div>

                <button type="submit" class="btn-pay">Pay & Enroll Now</button>
            </form>
        </div>
    </div>

</body>
</html>