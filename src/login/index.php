<?php
if(isset($_COOKIE["jwt"]))
{
    header("location: ../profile");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Login</title>
        <link rel="stylesheet" type="text/css" href="../style.css" />
        <link rel="stylesheet" type="text/css" href="login_style.css" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">

        <script src="../script.js" async></script>
        <script src="login_script.js" async></script>
        <!-- jQuery -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js" crossorigin="anonymous"></script>
        <!-- fontawesome icons -->
        <script src="https://kit.fontawesome.com/a4f543b8bc.js" crossorigin="anonymous"></script>
    </head>

    <body>
        <nav id="navbar" class="topnav">
            <a href=".." id="logo">Job Hunter</a>
            <a href="#" class="nav-tab">Recent jobs</a>
            <div class="right">
                <a href="#" class="current-page">Login</a>
                <a href="../create-account" class="nav-tab">Create account</a>
            </div>
            <a href="javascript:void(0);" class="icon" onclick="expand()">
                <i class="fa fa-bars"></i>
            </a>
        </nav>

        <div id="main">
            <div id="login-form" class="login-form">
                <p id="error-text">Text</p>
                
                <form action="#" method="post">
                    <div>
                        <label for="email">E-mail address</label>
                    </div>
                    <input type="text" id="email" name="email" class="input">

                    <div>
                        <label for="password">Password</label>
                    </div>
                    <input type="password" id="password" name="password" class="input">
                </form>

                <div class="button-container">
                    <button class="submit-button" onclick="checkValues()">Sign in</button>
                    
                    <p>Don't have an account? <a href="../create-account">Sign up</a></p>
                </div>
            </div>
        </div>
    </body>
</html>
