<?php
if(!isset($_GET["id"]))
{
    header("location: ..");
    exit();
}

require_once("../models/database.php");
require_once("../models/company_model.php");
require_once("../controllers/company_controller.php");

$company = new CompanyController($_GET["id"]);
$response = $company->getCompanyData();
if($response == -1 || $response == 0)
{
    include("page_not_found.html");
    exit();
}
else
    $title = "Reviews for " . $response["company_name"];

require_once("../controllers/jwt_controller.php");

$isCandidate = false;
if(isset($_COOKIE["jwt"]))
{
    $data = JWTController::getPayload($_COOKIE["jwt"]);
    if(JWTController::validateToken($_COOKIE["jwt"]) && $data["account_type"] == "candidate")
        $isCandidate = true;

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
        <link rel="stylesheet" type="text/css" href="../reviews/reviews_style.css" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta charset="UTF-8">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">

        <script src="../script.js" defer></script>
        <script src="../reviews/reviews_script.js" defer></script>
    </head>

    <body>
    <nav id="navbar" class="topnav">
            <a href="../" id="logo">JobHunter</a>
            <a href="../search" class="nav-tab">Recent jobs</a>
            <div class="right">
                <?php if(isset($_COOKIE["jwt"])): ?>
                    <a href="../profile">Profile</a>
                    <?php if($isCandidate): ?>
                        <a href="../my-jobs" class="nav-tab">My jobs</a>
                        <a href="../my-reviews" class="nav-tab">My reviews</a>
                    <?php else: ?>
                        <a href="../notifications">Notifications
                            <?php if($notifications > 0): ?>
                                <div class="notifs"><?php echo $notifications; ?></div>
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>
                    <a href="javascript:void(0)" class="nav-tab" onclick="logout()">Log out</a>
                <?php else: ?>
                    <a href="../login" class="nav-tab">Login</a>
                    <a href="../create-account" class="nav-tab">Create account</a>
                <?php endif; ?>
            </div>
            <a href="javascript:void(0);" class="icon" onclick="expand()">
                <i class="fa fa-bars"></i>
            </a>
        </nav>

        <div id="main">
            <button class="return-button" onclick="redirect(<?php echo $_GET['id'] ?>)">Back to company page</button>
            <div class="stars-container">
                <div id="company-rating">
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                </div>
                <p id="reviews-stats" class="skeleton skeleton-text-single" style="width: 60vw;"></p>
            </div>

            <div id="wrapper">
                <div class="reviews">
                    <div class="card">
                        <div class="top-row">
                            <h2 class="skeleton skeleton-text-single"></h2>
                        </div>
                        <div class="review-content">
                            <div class="skeleton skeleton-text"></div>
                            <div class="skeleton skeleton-text"></div>
                            <div class="skeleton skeleton-text"></div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="top-row">
                            <h2 class="skeleton skeleton-text-single"></h2>
                        </div>
                        <div class="review-content">
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
        <!-- Chart.js -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js" crossorigin="anonymous"></script>
        <!-- fontawesome icons -->
        <script src="https://kit.fontawesome.com/a4f543b8bc.js" crossorigin="anonymous"></script>
    </body>
</html>