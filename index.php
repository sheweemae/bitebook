<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BiteBook</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat:700,400&display=swap">
    <style>
        html, body {
            height: 100vh;
            width: 100vw;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
        body {
            font-family: 'Montserrat', Arial, sans-serif;
            background: #f8f8f8;
            height: 100vh;
            width: 100vw;
        }
        .container{
            display: flex;
            flex-direction: column;
            height: 100vh;
            width: 100vw;
        }
        .topbar {
            width: 100%;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
            height: 8vh;
            box-sizing: border-box;
            border-bottom: 1px solid #eee;
        }
        .copyright {
            font-size: 14px;
            color: #888;
        }
        .appname {
            font-family: 'Montserrat', cursive;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 2px;
            color: #222;
        }
        .topbar-buttons {
            display: flex;
            gap: 10px;
        }
        .topbar-buttons form {
            margin: 0;
        }
        .topbar-buttons input[type="submit"] {
            background: #fff;
            border: 1px solid #222;
            border-radius: 4px;
            padding: 6px 18px;
            font-size: 15px;
            font-family: 'Montserrat', Arial, sans-serif;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s, color 0.2s;
        }
        .topbar-buttons input[type="submit"]:hover {
            background: #222;
            color: #fff;
        }
        .welcome-section {
            position: relative;
            width: 100%;
            height: 100vh;
            background: url("images/background/bg0.svg") center center/cover no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .welcome-content {
            position: relative;
            z-index: 2;
            color: #fff;
            text-align: center;
            max-width: 600px;
            margin: 0 auto;
        }
        .welcome-content h1 {
            font-size: 4rem;
            font-weight: 700;
            margin-bottom: 18px;
            letter-spacing: 1px;
        }
        .welcome-content p {
            font-size: 1.2rem;
            font-weight: 400;
            line-height: 1.5;
        }
        @media (max-width: 700px) {
            .welcome-content h1 { font-size: 2rem; }
            .welcome-content p { font-size: 1rem; }
            .topbar { flex-direction: column; height: auto; padding: 10px; }
        }
            .appname {
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 1;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="topbar">
            <div class="copyright">Made in 2025</div>
            <div class="appname">
                <img src="images/text/appname.svg" alt="BiteBook" style="height:36px; display:block; margin:auto;">
            </div>
            <div class="topbar-buttons">
                <form action="login.php" method="post">
                    <input type="submit" value="Log In">
                </form>
                <form action="register.php" method="post">
                    <input type="submit" value="Sign Up">
                </form>
            </div>
        </div>
        <div class="welcome-section">
            <div class="welcome-content">
                <h1>YOUR RECIPE COMPANION</h1>
                <p>
                    Say goodbye to scattered notes and hello to an organized digital cookbook.
                    Effortlessly save, find, and enjoy your favorite dishes with BiteBook – your personal recipe keeper made easy.
                </p>
            </div>
        </div>
    </div>
</body>
</html>