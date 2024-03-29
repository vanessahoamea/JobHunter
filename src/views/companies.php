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
    $title = $response["company_name"];

require_once("../controllers/jwt_controller.php");

$selfView = false;
$isCandidate = false;
if(isset($_COOKIE["jwt"]))
{
    $data = JWTController::getPayload($_COOKIE["jwt"]);
    if(JWTController::validateToken($_COOKIE["jwt"]) && $data["id"] == $_GET["id"] && $data["account_type"] == "company")
        $selfView = true;
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
        <link rel="stylesheet" type="text/css" href="../profile/company_profile_style.css" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta charset="UTF-8">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">

        <script src="../script.js" defer></script>
        <script src="../profile/company_profile_script.js" defer></script>
    </head>

    <body>
        <!-- to help the script -->
        <?php if($selfView): ?>
            <div id="self-view" style="display: none;"></div>
        <?php endif; ?>

        <nav id="navbar" class="topnav">
            <a href="../" id="logo">JobHunter</a>
            <a href="../search" class="nav-tab">Recent jobs</a>
            <div class="right">
                <?php if(isset($_COOKIE["jwt"])): ?>
                    <?php if($selfView): ?>
                        <a href="#" class="current-page">Profile</a>
                    <?php else: ?>
                        <a href="../profile">Profile</a>
                    <?php endif; ?>
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

        <div id="profile">
            <div id="company-information">
                <div class="profile-picture">
                    <img class="profile-picture skeleton" src="../assets/default.jpg" alt="Profile picture."></img>
                </div>
                <h1 id="company-name"><div class="skeleton skeleton-text-single name"></div></h1>
                <div class="information-list">
                    <div class="skeleton skeleton-text"></div>
                    <div class="skeleton skeleton-text"></div>
                </div>

                <?php if($selfView): ?>
                    <button class="edit-button" onclick="window.location.href = '../settings'">Edit profile</button>
                <?php elseif($isCandidate || !isset($_COOKIE["jwt"])): ?>
                    <button class="edit-button" onclick="window.location.href = '../post-review?id=<?php echo $_GET['id']; ?>'">Review this company</button>
                <?php endif; ?>
            </div>

            <div id="rating">
                <div class="upper-part">
                    <h1>Rating</h1>
                </div>

                <div class="stars-container">
                    <div id="company-rating">
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                    </div>
                </div>
                
                <button class="edit-button" style="width: fit-content;" onclick="window.location.href = '../reviews?id=<?php echo $_GET['id']; ?>'">See all company reviews</button>
            </div>

            <div id="job-postings">
                <div class="upper-part">
                    <h1>Job postings</h1>
                    <?php if($selfView): ?>
                        <div>
                            <button class="edit-button" onclick="window.location.href = '../post-job'">
                                <i class="fa-solid fa-plus fa-fw"></i>Add new
                            </button>
                        </div>
                    <?php endif; ?>
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
                
                <div id="pagination"></div>
            </div>
        </div>

        <!-- delete item modal -->
        <div class="modal">
            <div class="modal-content">
                <span class="close" onclick="toggleModal()">&times;</span>

                <div class="modal-wrapper">
                    <p>Are you sure you want to delete this job posting?</p>
                    <button class="edit-button" onclick="deleteJob()">Delete</button>
                </div>
            </div>
        </div>

        <!-- jQuery -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js" crossorigin="anonymous"></script>
        <!-- fontawesome icons -->
        <script src="https://kit.fontawesome.com/a4f543b8bc.js" crossorigin="anonymous"></script>
    </body>
</html>