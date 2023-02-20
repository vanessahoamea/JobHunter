<?php
if(!isset($_COOKIE["jwt"]))
{
    header("location: ..");
    exit();
}
else
{
    require_once("../controllers/jwt_controller.php");

    $data = JWTController::getPayload($_COOKIE["jwt"]);
    if(!JWTController::validateToken($_COOKIE["jwt"]) || $data["account_type"] != "candidate")
    {
        header("location: ..");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <title>My jobs</title>
        <link rel="stylesheet" type="text/css" href="../style.css" />
        <link rel="stylesheet" type="text/css" href="my_jobs_style.css" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta charset="UTF-8">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">

        <script src="../script.js" defer></script>
        <script src="my_jobs_script.js" defer></script>
    </head>

    <body>
        <nav id="navbar" class="topnav">
            <a href="../" id="logo">JobHunter</a>
            <a href="../search" class="nav-tab">Recent jobs</a>
            <div class="right">
                <a href="../profile" class="nav-tab">Profile</a>
                <a href="#" class="current-page">My jobs</a>
                <a href="../my-reviews" class="nav-tab">My reviews</a>
                <a href="javascript:void(0)" class="nav-tab" onclick="logout(false)">Log out</a>
            </div>
            <a href="javascript:void(0);" class="icon" onclick="expand()">
                <i class="fa fa-bars"></i>
            </a>
        </nav>

        <div id="main">
            <div class="top-buttons">
                <button class="search-button" onclick="showJobs(0)">Bookmarked</button>
                <button class="search-button" onclick="showJobs(1)">Hidden</button>
                <button class="search-button" onclick="showJobs(2)">Applied to</button>
            </div>

            <div id="wrapper">
                <div class="jobs">
                    <div class="card">
                        <div class="top-row">
                            <h2 class="skeleton skeleton-text-single"></h2>
                        </div>
                        <div class="card-information">
                            <div class="skeleton skeleton-text"></div>
                            <div class="skeleton skeleton-text"></div>
                            <div class="skeleton skeleton-text"></div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="top-row">
                            <h2 class="skeleton skeleton-text-single"></h2>
                        </div>
                        <div class="card-information">
                            <div class="skeleton skeleton-text"></div>
                            <div class="skeleton skeleton-text"></div>
                            <div class="skeleton skeleton-text"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- modal -->
        <div class="modal">
            <div class="modal-content">
                <span class="close" onclick="toggleModal()">&times;</span>

                <div class="modal-wrapper" style="text-align: center;"></div>
            </div>
        </div>
        
        <!-- jQuery -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js" crossorigin="anonymous"></script>
        <!-- fontawesome icons -->
        <script src="https://kit.fontawesome.com/a4f543b8bc.js" crossorigin="anonymous"></script>
    </body>
</html>