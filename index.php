<?php
include './staff/db.php'; // Database connection

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email_or_phone = trim($_POST["email_or_phone"]);
    $password = trim($_POST["password"]);

    // Check if the user exists (match email or phone)
    $sql = "SELECT id, full_name, email, phone, password FROM admins WHERE email = ? OR phone = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email_or_phone, $email_or_phone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $admin["password"])) {
            $_SESSION["admin_id"] = $admin["id"];
            $_SESSION["admin_name"] = $admin["full_name"];
            echo "<script>alert('Login successful!'); window.location='./staff/create_invoice.php';</script>";
        } else {
            echo "<script>alert('Invalid credentials!');</script>";
        }
    } else {
        echo "<script>alert('Admin not found!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Login </title>
    <?php include './staff/cdn.php' ?>
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/signup.css">
</head>

<body>
    <div class="register_all">
        <div class="forms_all">

        <br>
        <div class="register_title">
            <h2>Admin Login</h2>
        </div>
            <form method="POST">
                <div class="forms">
                    <label>Email or Phone</label>
                    <input type="text" name="email_or_phone" required>
                </div>

                <div class="forms">
                    <label>Password</label>
                    <input type="password" name="password" id="password" required>
                </div>

                <div class="form">
                    <input type="checkbox" id="showPassword">
                    Show password
                </div>

               <div class="forms">
               <button type="submit">Login</button>
               </div>
               
            </form>
            <div class="forms forgot_password">
                    <p><a href="./staff/forgot_password.php">Forgot your password?</a></p>
                  <!-- <p>Don't have an account? <a href="signup.php">Signup</a></p>
                    <p>Return home <a href="index.php">Click here</a></p> -->
                </div>
        </div>
    </div>

    <script>
        // Show password toggle
        document.getElementById('showPassword').addEventListener('change', function() {
            var passwordInput = document.getElementById('password');
            passwordInput.type = this.checked ? 'text' : 'password';
        });
    </script>

</body>

</html>