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

    $login_success = null;
    $error_message = '';

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login'], $_POST['email'], $_POST['password'])) {
        $email = mysqli_real_escape_string($connect, $_POST['email']);
        $password = $_POST['password']; // password_verify expects raw password, no need to escape

        $query = "SELECT * FROM users WHERE email='$email' LIMIT 1";
        $result = mysqli_query($connect, $query);

        if ($result && mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_email'] = $user['email']; // Store email in session
                $_SESSION['user_id'] = $user['user_id']; // Store user ID in session
                $login_success = true;
            } else {
                $login_success = false;
                $error_message = "Invalid password. Please try again.";
            }
        } else {
            $login_success = false;
            $error_message = "Email not found.";
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
    <link rel="stylesheet" href="css/login_register.css" />
</head>
<body>
    <div class="login-main-container">
        <div class="login-image">
            <img src="images/background/bg1.svg" alt="Login Food" />
        </div>
        <div class="login-card-holder">
            <div class="login-card">
                <div class="icon">
                    <div class="icon">
                        <img src="images/text/logo.svg" alt="Chef Hat Logo" style="height:32px; width:auto; display:block; margin:auto;">
                    </div>
                </div>
                <h2>Log In</h2>
                <div class="top-links">
                    Don't have an account?
                    <a href="register.php">Create New Account</a>
                </div>
                <form action="login.php" method="post" autocomplete="off">
                    <div class="fields">
                        <input type="text" id="email" name="email" placeholder="Email" required>
                        <div class="password-field">
                            <input type="password" id="password" name="password" placeholder="Password" required>
                            <button type="button" class="password-toggle" onclick="togglePassword()">
                                <span id="toggle-icon">&#128065;</span> <!-- eye icon -->
                                <span id="toggle-text" style="font-size:0.95em;">Show</span>
                            </button>
                        </div>
                    </div>
                    <button type="submit" name="login" value="1">Log In</button>
                </form>
                <div class="terms">
                    By continuing, you agree to the
                    <a href="#">Terms of Use</a>
                    and
                    <a href="#">Privacy Policy</a>.
                </div>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function togglePassword() {
        var passwordField = document.getElementById('password');
        var toggleIcon = document.getElementById('toggle-icon');
        var toggleText = document.getElementById('toggle-text');

        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            toggleIcon.innerHTML = '&#128064;'; // eye-off icon
            toggleText.innerHTML = 'Hide';
        } else {
            passwordField.type = 'password';
            toggleIcon.innerHTML = '&#128065;'; // eye icon
            toggleText.innerHTML = 'Show';
        } 
    }
    <?php if ($login_success === true): ?>
        Swal.fire({
            title: 'Login Successful!',
            icon: 'success',
            confirmButtonText: 'Continue',
            confirmButtonColor: '#222',     // changes button color
            color: '#222',                  // text color
            customClass: {
                popup: 'my-popup',
                title: 'my-title',
                confirmButton: 'my-confirm-btn'
            }
        }).then(() => {
            window.location.href = 'dashboard.php'; // Redirect after OK
        });
    <?php elseif ($login_success === false): ?>
        Swal.fire({
            title: 'Login Failed!',
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
</body>
</html>
