<?php
include 'db.php'; // Database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST["full_name"]);
    $phone = trim($_POST["phone"]);
    $email = trim($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    // Check if email already exists
    $check_email = $conn->prepare("SELECT id FROM admins WHERE email = ?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    $check_email->store_result();

    if ($check_email->num_rows > 0) {
        echo "<script>alert('Email already registered!'); window.location='signup.php';</script>";
    } else {
        // Insert new admin
        $sql = "INSERT INTO admins (full_name, phone, email, password) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $full_name, $phone, $email, $password);

        if ($stmt->execute()) {
            echo "<script>alert('Admin registered successfully!'); window.location='login.php';</script>";
        } else {
            echo "<script>alert('Error registering admin!');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
    <?php include 'cdn.php'; ?>
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/signup.css">
</head>
<body>

    <div class="register_all">
        <div class="forms_all">
       
            <br>
            <div class="register_title">
                <h2>Admin Signup</h2>
            </div>
            <form method="POST">
                
                <div class="forms">
                    <label>Full Name</label>
                    <input type="text" name="full_name" required>
                </div>

                <div class="forms">
                    <label>Phone</label>
                    <input type="tel" name="phone" required>
                </div>

                <div class="forms">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>

                <div class="forms">
                    <label>Password</label>
                    <input type="password" name="password" id="password" required>
                </div>

                <div class="show_password">
                    <input type="checkbox" id="showPassword"> Show password
                </div>

                <div class="forms">
                    <button type="submit">Register</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Show password toggle
        document.getElementById('showPassword').addEventListener('change', function() {
            let passwordField = document.getElementById('password');
            passwordField.type = this.checked ? 'text' : 'password';
        });
    </script>

</body>
</html>