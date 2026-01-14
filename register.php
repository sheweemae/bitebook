<?php
session_start();

$host = 'localhost'; // Replace with your database host
$username = "root"; 
$password = ""; 
$dbname = "bitebook_users";

$connect = mysqli_connect($host, $username, $password, $dbname);
if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

$email_exists = false;
$invalid_email = false;
$register_success = false;
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_register'])) {
    $fname = isset($_POST['fname']) ? mysqli_real_escape_string($connect, $_POST['fname']) : '';
    $lname = isset($_POST['lname']) ? mysqli_real_escape_string($connect, $_POST['lname']) : '';
    $email = isset($_POST['email']) ? mysqli_real_escape_string($connect, $_POST['email']) : '';
    $password = isset($_POST['password']) ? mysqli_real_escape_string($connect, $_POST['password']) : '';
   
    // Validate email format first
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $invalid_email = true;
        $error_message = "Invalid Email Format!";
    } else {
        // Check if email already exists
        $check_email = "SELECT * FROM users WHERE email='$email'";
        $result = mysqli_query($connect, $check_email);
        if (mysqli_num_rows($result) > 0) {
            $email_exists = true;
            $error_message = "Email Already Exists!";
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Insert user into the database
            $sql = "INSERT INTO users (fname, lname, email, password) VALUES ('$fname', '$lname', '$email', '$hashed_password')";
            if (mysqli_query($connect, $sql)) {
                $register_success = true;
            } else {
                $invalid_email = true; // Treat DB errors as general failure
                $error_message = "Database error: " . mysqli_error($connect);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Bitebook Login</title>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/login_register.css"/>
</head>
<body>
    <div class="register-main-container">
        <div class="register-image">
            <img src="images/background/bg1.svg" alt="Register Food" />
        </div>
        <div class="register-card-holder">
            <div class="register-card">
                <div class="icon">
                    <img src="images/text/logo.svg" alt="Chef Hat Logo">
                </div>
                <h2>Create an account</h2>
                <div class="top-links">
                    Already have an account?
                    <a href="login.php">Log in</a>
                </div>
                <form action="register.php" method="post" autocomplete="off" id="registerForm">
                    <div class="name-fields">
                        <input type="text" id="fname" name="fname" placeholder="First name" required>
                        <input type="text" id="lname" name="lname" placeholder="Last name" required>
                    </div>
                    <input type="text" id="email" name="email" placeholder="Email address" required>
                    <input type="password" id="password" name="password" placeholder="Password" required>
                    <div class="password-hint">
                        Use 8 or more characters
                    </div>
                    <div class="show-password-row">
                        <input type="checkbox" id="checkboxpass">
                        <label for="checkboxpass" style="margin:0; font-weight:400; cursor:pointer;">Show password</label>
                    </div>
                    <button type="submit" name="submit_register" value="1" id="registerBtn" disabled>Create an account</button>
                </form>
                <button class="login-instead-link" onclick="window.location.href='login.php'">log in instead</button>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>

    // Show/hide password
    document.getElementById('checkboxpass').addEventListener('change', function() {
        var passwordField = document.getElementById('password');
        passwordField.type = this.checked ? 'text' : 'password';
    });

    // Enable button only if password is 8+ chars and all fields filled
    function validateForm() {
        var fname = document.getElementById('fname').value.trim();
        var lname = document.getElementById('lname').value.trim();
        var email = document.getElementById('email').value.trim();
        var password = document.getElementById('password').value;
        var btn = document.getElementById('registerBtn');
        btn.disabled = !(fname && lname && email && password.length >= 8);
    }
    document.getElementById('registerForm').addEventListener('input', validateForm);
<?php if ($register_success): ?>
    Swal.fire({
        title: 'Registration Successful!',
        icon: 'success',
        showConfirmButton: true,
        confirmButtonText: 'Continue',
        confirmButtonColor: '#222',     // changes button color
        color: '#222',                  // text color
        customClass: {
            popup: 'my-popup',
            title: 'my-title',
            confirmButton: 'my-confirm-btn'
        }
    }).then(() => {
        window.location.href = 'login.php';
    });
<?php elseif ($invalid_email || $email_exists): ?>
    Swal.fire({
        title: 'Registration Failed!',
        icon: 'error',
        text: <?php echo json_encode($error_message); ?>,
        confirmButtonText: 'Try Again',
        confirmButtonColor: '#222',     // changes button color
        color: '#222',                  // text color
        customClass: {
            popup: 'my-popup',
            title: 'my-title',
            confirmButton: 'my-confirm-btn'
        }
    });
<?php endif; ?>
</script>

<script>
    document.getElementById('checkboxpass').addEventListener('change', function() {
        var passwordField = document.getElementById('password');
        if (this.checked) {
            passwordField.type = 'text';
        } else {
            passwordField.type = 'password';
        }
    });   
</script>

</body>
</html>
