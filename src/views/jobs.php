<?php
if(!isset($_GET["id"]))
{
    header("location: ../");
    exit();
}

require_once("../models/database.php");
require_once("../models/company_model.php");
require_once("../controllers/company_controller.php");

$company = new CompanyController(0);
$response = $company->getJob($_GET["id"]);
if($response == 0 || $response == -1)
{
    include("page_not_found.html");
    exit();
}
else
    $title = $response["title"];

require_once("../controllers/jwt_controller.php");

$canApply = true;
$ownJob = false;
if(isset($_COOKIE["jwt"]))
{
    $data = JWTController::getPayload($_COOKIE["jwt"]);
    if(JWTController::validateToken($_COOKIE["jwt"]))
    {
        if($data["account_type"] == "company")
            $canApply = false;
        if($data["account_type"] == "company" && $data["id"] == $response["company_id"])
            $ownJob = true;
    }

    //notifications
    if($data["account_type"] == "company")
    {
        $company = new CompanyController($data["id"]);
        $notifications = $company->getNotificationCount();
        if($notifications == 0)
            $notifications = 0;
        else
            $notifications = min(99, $notifications["unread_notifications"]);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <title><?php echo $title; ?></title>
        <link rel="stylesheet" type="text/css" href="../style.css" />
        <link rel="stylesheet" type="text/css" href="../jobs/jobs_style.css" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta charset="UTF-8">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">

        <script src="../script.js" defer></script>
        <script src="../jobs/jobs_script.js" defer></script>
    </head>

    <body>
        <nav id="navbar" class="topnav">
            <a href="../" id="logo">JobHunter</a>
            <a href="../search" class="nav-tab">Recent jobs</a>
            <?php if(!isset($_COOKIE["jwt"])): ?>
                <div class="right">
                    <a href="../login" class="nav-tab">Login</a>
                    <a href="../create-account" class="nav-tab">Create account</a>
                </div>
            <?php else: ?>
                <div class="right">
                    <a href="../profile" class="nav-tab">Profile</a>
                    <?php if($canApply): ?>
                        <a href="../my-jobs" class="nav-tab">My jobs</a>
                        <a href="../my-reviews" class="nav-tab">My reviews</a>
                    <?php else: ?>
                        <a href="../notifications">Notifications
                            <?php if($notifications > 0): ?>
                                <div class="notifs"><?php echo $notifications; ?></div>
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>
                    <a href="javascript:void(0)" class="nav-tab" onclick="logout(false)">Log out</a>
                </div>
            <?php endif; ?>
            <a href="javascript:void(0);" class="icon" onclick="expand()">
                <i class="fa fa-bars"></i>
            </a>
        </nav>

        <div id="main">
            <div id="wrapper">
                <div class="upper-part">
                    <div class="company-picture">
                        <img class="company-picture skeleton" src="../assets/default.jpg" alt="Company picture"></img>
                    </div>

                    <div style="width: 100%;">
                        <div class="job-data-upper">
                            <h2 class="skeleton skeleton-text-single"></h2>
                        </div>
                        <div class="job-data-lower">
                            <div class="skeleton skeleton-text"></div>
                            <div class="skeleton skeleton-text"></div>
                            <div class="skeleton skeleton-text"></div>
                        </div>
                    </div>
                </div>

                <div class="job-description">
                    <div id="skills-section">
                        <h2>Skills required</h2>
                        <div style="margin-bottom: .25rem;">
                            <div class="skeleton skeleton-text"></div>
                            <div class="skeleton skeleton-text"></div>
                            <div class="skeleton skeleton-text"></div>
                        </div>
                    </div>

                    <div id="description-section">
                        <h2>Job description</h2>
                        <div style="margin-bottom: .25rem;">
                            <div class="skeleton skeleton-text"></div>
                            <div class="skeleton skeleton-text"></div>
                            <div class="skeleton skeleton-text"></div>
                        </div>

                        <div>
                            <div class="skeleton skeleton-text"></div>
                            <div class="skeleton skeleton-text"></div>
                            <div class="skeleton skeleton-text"></div>
                        </div>
                    </div>
                </div>

            <?php if($canApply): ?>
                <div class="buttons">
                    <button class="search-button" onclick="toggleModal(1)">Apply</button>
                    <button class='search-button' onclick='toggleModal(2)'>Save</button>
                    <button class='search-button' onclick='toggleModal(3)'>Hide</button>
                </div>
            <?php endif; ?>

            <?php if($ownJob): ?>
                <button class="search-button company-button" onclick="redirect()">View applicants</button>
            <?php endif; ?>
            </div>
        </div>

        <!-- apply for job modal -->
        <div class="modal">
            <div class="modal-content">
                <span class="close" onclick="toggleModal(null)">&times;</span>

                <div class="modal-wrapper" style="text-align: center;"></div>
            </div>
        </div>
        
        <!-- jQuery -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js" crossorigin="anonymous"></script>
        <!-- fontawesome icons -->
        <script src="https://kit.fontawesome.com/a4f543b8bc.js" crossorigin="anonymous"></script>
    </body>
</html>