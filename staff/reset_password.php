<?php
include 'db.php';

$error_message = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $reset_code = $_POST['reset_code'];
    $new_password = $_POST['new_password'];

    // Check if email and reset code match in the database
    $sql = "SELECT * FROM admins WHERE email = ? AND reset_code = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $reset_code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update the password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_sql = "UPDATE admins SET password = ?, reset_code = NULL WHERE email = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ss", $hashed_password, $email);
        $update_stmt->execute();

        $success_message = "Your password has been reset. You can now log in with your new password.";
        header("Location: login.php?reset=success");
        exit();
    } else {
        $error_message = "Invalid email or reset code.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <?php include 'cdn.php'; ?>
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/signup.css">
</head>
<body>
<div class="register_all">
    <div class="forms_all">
        <div class="logo"></div>
        <div class="forms_title">
           <h2>Reset Password</h2>
        </div>
        <?php if ($error_message != ""): ?>
            <div class="error_message error" id="error-message">
                <?php echo $error_message; ?>
                <span class="close-btn" onclick="closeError()">x</span>
            </div>
        <?php endif; ?>
        <?php if ($success_message != ""): ?>
            <div class="success_message" id="success-message">
                <?php echo $success_message; ?>
                <span class="close-btn" onclick="closeSuccess()">x</span>
            </div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="forms">
                <label>Email: </label>
                <input type="email" placeholder="Enter your email" name="email" required>
            </div>
            <div class="forms">
                <label>Reset Code: </label>
                <input type="text" placeholder="Enter the reset code" name="reset_code" required>
            </div>
            <div class="forms">
                <label>New Password: </label>
                <input type="password" id="password" placeholder="Enter your new password" name="new_password" required>
            </div>
            <div class="show_password">
                <input type="checkbox" id="showPassword">
                Show password
            </div>
            <div class="forms">
                <button type="submit">Reset Password</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
<script>

        // Show password toggle
        document.getElementById('showPassword').addEventListener('change', function() {
        var pinInput = document.getElementById('password');
        pinInput.type = this.checked ? 'text' : 'password';
    });

    // Close error message with animation
    function closeError() {
        anime({
            targets: '#error-message',
            opacity: 0,
            translateY: -20,
            duration: 500,
            easing: 'easeOutExpo',
            complete: function() {
                document.getElementById('error-message').style.display = 'none';
            }
        });
    }

    // Close success message with animation
    function closeSuccess() {
        anime({
            targets: '#success-message',
            opacity: 0,
            translateY: -20,
            duration: 500,
            easing: 'easeOutExpo',
            complete: function() {
                document.getElementById('success-message').style.display = 'none';
            }
        });
    }
</script>
</body>
</html>