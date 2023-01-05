<?php
if(!isset($_COOKIE["jwt"]))
{
    header("location: ../login");
    exit();
}

require_once("../models/database.php");
require_once("../models/company_model.php");
require_once("../controllers/company_controller.php");
require_once("../controllers/jwt_controller.php");

$data = JWTController::getPayload($_COOKIE["jwt"]);
if(!isset($_GET["id"]) || !JWTController::validateToken($_COOKIE["jwt"]) || $data["account_type"] != "company")
{
    header("location: ../");
    exit();
}
else
{
    $company = new CompanyController($data["id"]);
    if(!$company->validate($_GET["id"]))
    {
        header("location: ../");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Applicants</title>
        <link rel="stylesheet" type="text/css" href="../style.css" />
        <link rel="stylesheet" type="text/css" href="applicants_style.css" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">

        <script src="../script.js" async></script>
        <script src="applicants_script.js" defer></script>
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
                    <a href="javascript:void(0)" class="nav-tab" onclick="logout(false)">Log out</a>
                </div>
            <?php endif; ?>
            <a href="javascript:void(0);" class="icon" onclick="expand()">
                <i class="fa fa-bars"></i>
            </a>
        </nav>

        <div id="main">
            <div id="wrapper">
                <div class="candidates">
                    <div class="candidate-card">
                        <div class="candidate-data">
                            <div class="candidate-picture">
                                <img class="candidate-picture skeleton" src="../assets/default.jpg" alt="Candidate picture">
                            </div>
                            <div style="width: 100%;">
                                <div class="skeleton skeleton-text"></div>
                                <div class="skeleton skeleton-text"></div>
                                <div class="skeleton skeleton-text"></div>
                            </div>
                        </div>
                    </div>

                    <div class="candidate-card">
                        <div class="candidate-data">
                            <div class="candidate-picture">
                                <img class="candidate-picture skeleton" src="../assets/default.jpg" alt="Candidate picture">
                            </div>
                            <div style="width: 100%;">
                                <div class="skeleton skeleton-text"></div>
                                <div class="skeleton skeleton-text"></div>
                                <div class="skeleton skeleton-text"></div>
                            </div>
                        </div>
                    </div>

                    <div class="candidate-card">
                        <div class="candidate-data">
                            <div class="candidate-picture">
                                <img class="candidate-picture skeleton" src="../assets/default.jpg" alt="Candidate picture">
                            </div>
                            <div style="width: 100%;">
                                <div class="skeleton skeleton-text"></div>
                                <div class="skeleton skeleton-text"></div>
                                <div class="skeleton skeleton-text"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- candidate information modal -->
        <div class="modal">
            <div class="modal-content">
                <span class="close" onclick="toggleModal(null)">&times;</span>

                <div class="modal-wrapper" style="text-align: center;">
                    <p>...</p>
                    <button class="search-button" onclick="toggleModal(null)">Close</button>
                </div>
            </div>
        </div>
        
        <!-- jQuery -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js" crossorigin="anonymous"></script>
        <!-- fontawesome icons -->
        <script src="https://kit.fontawesome.com/a4f543b8bc.js" crossorigin="anonymous"></script>
    </body>
</html>