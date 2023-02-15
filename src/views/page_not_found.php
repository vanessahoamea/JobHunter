<?php
$isCandidate = false;
if(isset($_COOKIE["jwt"]))
{
    require_once("../controllers/jwt_controller.php");

    if(JWTController::validateToken($_COOKIE["jwt"]));
    {
        $data = JWTController::getPayload($_COOKIE["jwt"]);
        if($data["account_type"] == "candidate")
            $isCandidate = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Error 404</title>
        <link rel="stylesheet" type="text/css" href="../style.css" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">

        <script src="../script.js" async></script>
    </head>

    <body>
        <nav id="navbar" class="topnav">
            <a href="#" id="logo">JobHunter</a>
            <a href="search" class="nav-tab">Recent jobs</a>
            <?php if(!isset($_COOKIE["jwt"])): ?>
                <div class="right">
                    <a href="login" class="nav-tab">Login</a>
                    <a href="create-account" class="nav-tab">Create account</a>
                </div>
            <?php else: ?>
                <div class="right">
                    <a href="profile" class="nav-tab">Profile</a>
                    <?php if($isCandidate): ?>
                        <a href="my-jobs" class="nav-tab">My jobs</a>
                    <?php endif; ?>
                    <a href="javascript:void(0)" class="nav-tab" onclick="logout(true)">Log out</a>
                </div>
            <?php endif; ?>
            <a href="javascript:void(0);" class="icon" onclick="expand()">
                <i class="fa fa-bars"></i>
            </a>
        </nav>

        <div id="main" >
            <h1 style="font-size: 4em; letter-spacing: 3px;">Oops.</h1>
            <h3 style="text-transform: uppercase;">404 - Page not found</h3>
            <p class="p-text">Sorry, we could not find the page you were looking for. Make sure you followed a correct link.</p>
            <button class="return-button" onclick="window.location = '..'">Go to homepage</button>
        </div>
        
        <!-- fontawesome icons -->
        <script src="https://kit.fontawesome.com/a4f543b8bc.js" crossorigin="anonymous"></script>
    </body>

    <style>
        .return-button {
            min-height: 8vh;
            min-width: 10vw;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            background-color: black;
            color: white;
            margin-bottom: 3vh;
        }

        .p-text {
            width: 40vw;
            margin-left: auto;
            margin-right: auto;
        }
    </style>
</html>