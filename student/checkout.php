<?php
session_start();
require_once '../DB/db_connect.php'; // Uses: $servername, $username, $password, $dbname

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
    header("Location: my_courses.php");
    exit();
}

// 2. Fetch REAL course details including Price
$course_stmt = $conn->prepare("SELECT Title, Instructor, Price FROM courses WHERE CourseID = ?");
$course_stmt->bind_param("i", $course_id);
$course_stmt->execute();
$course_result = $course_stmt->get_result();

if ($course_result->num_rows === 0) {
    die("Course not found.");
}
$course = $course_result->fetch_assoc();

// Dynamic Price from Database
$course_price = floatval($course['Price']); 

$error_msg = '';

// 3. Handle Payment and Enrollment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn->begin_transaction();
    try {
        $payment_status = 'Completed';
        $payment_date = date('Y-m-d H:i:s');
        
        $pay_stmt = $conn->prepare("INSERT INTO payments (Amount, Date, Status, UserID, CourseID) VALUES (?, ?, ?, ?, ?)");
        $pay_stmt->bind_param("dssii", $course_price, $payment_date, $payment_status, $user_id, $course_id);
        $pay_stmt->execute();

        $enroll_stmt = $conn->prepare("INSERT INTO enrollments (UserID, CourseID) VALUES (?, ?)");
        $enroll_stmt->bind_param("ii", $user_id, $course_id);
        $enroll_stmt->execute();

        $conn->commit();
        header("Location: my_courses.php?status=enrolled");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $error_msg = "Transaction failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout | Success Hub</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        body { background-color: #f3f4f6; font-family: sans-serif; }
        .checkout-container { max-width: 850px; margin: 60px auto; display: grid; grid-template-columns: 1fr 1fr; background: white; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); overflow: hidden; }
        .order-summary { background: #010d1c; color: white; padding: 40px; }
        .payment-form { padding: 40px; }
        .price-tag { font-size: 32px; font-weight: 800; color: #4a90e2; margin: 20px 0; }
        .form-control { width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 6px; margin-top: 5px; }
        .row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .btn-pay { width: 100%; padding: 15px; background: #4a90e2; color: white; border: none; border-radius: 6px; font-weight: 700; cursor: pointer; margin-top: 20px; }
    </style>
</head>
<body>

    <div class="checkout-container">
        <div class="order-summary">
            <h2>Order Summary</h2>
            <p style="margin-top:20px; opacity:0.8;">Course:</p>
            <h3 style="margin-bottom:20px;"><?php echo htmlspecialchars($course['Title']); ?></h3>
            <p style="opacity:0.8;">Instructor:</p>
            <p><?php echo htmlspecialchars($course['Instructor']); ?></p>
            <div class="price-tag"><?php echo number_format($course_price, 2); ?> L.E.</div>
        </div>

        <div class="payment-form">
            <h2>Payment Details</h2>
            <?php if($error_msg) echo "<p style='color:red;'>$error_msg</p>"; ?>
            <form method="POST">
                <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                
                <div style="margin-bottom:15px;">
                    <label>Card Number</label>
                    <input type="text" id="cardNumber" class="form-control" placeholder="0000 0000 0000 0000" maxlength="19" required>
                </div>
                
                <div class="row">
                    <div>
                        <label>Expiry Date</label>
                        <input type="text" id="expiryDate" class="form-control" placeholder="MM/YY" maxlength="5" required>
                    </div>
                    <div>
                        <label>CVV</label>
                        <input type="text" class="form-control" placeholder="123" maxlength="3" required>
                    </div>
                </div>

                <button type="submit" class="btn-pay">Confirm Payment</button>
            </form>
        </div>
    </div>

    <script>
        // Automatic space after every 4 digits in Card Number
        document.getElementById('cardNumber').addEventListener('input', function (e) {
            let target = e.target;
            let position = target.selectionEnd;
            let length = target.value.length;
            
            target.value = target.value.replace(/[^\d]/g, '').replace(/(.{4})/g, '$1 ').trim();
            
            if(target.value.length > length) position++;
            target.setSelectionRange(position, position);
        });

        // Automatic slash in Expiry Date
        document.getElementById('expiryDate').addEventListener('input', function (e) {
            let value = e.target.value.replace(/[^\d]/g, '');
            if (value.length > 2) {
                e.target.value = value.substring(0, 2) + '/' + value.substring(2, 4);
            } else {
                e.target.value = value;
            }
        });
    </script>
</body>
</html>