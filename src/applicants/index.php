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
    if(!$company->validate($_GET["id"], "jobs"))
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
        <meta charset="UTF-8">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">

        <script src="../script.js" defer></script>
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
            <button class="return-button" onclick="redirect(<?php echo $_GET['id'] ?>, 1)">Back to job page</button>
            <div class="filters">
                <h2>Filter candidates</h2>

                <h3>Minimum years of experience</h3>
                <label for="minimum-years">
                    <input class="input" type="number" id="minimum-years" name="minimum-years" min="0" value="0">
                </label>

                <h3>Others</h3>
                <label for="sort">
                    <input type="checkbox" id="sort" name="sort"> Sort by amount of experience
                </label>
                <label for="job-hoppers">
                    <input type="checkbox" id="job-hoppers" name="job-hoppers"> Hide candidates that might be job hoppers
                </label>
                <label for="descriptions">
                    <input type="checkbox" id="descriptions" name="descriptions"> Hide candidates that don't provide descriptions for previous roles
                </label>

                <div class="buttons">
                    <button class="search-button" onclick="applyFilters(<?php echo $_GET['id'] ?>)">Apply</button>
                </div>
            </div>

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

                <div class="modal-wrapper" style="text-align: center;"></div>
            </div>
        </div>
        
        <!-- jQuery -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js" crossorigin="anonymous"></script>
        <!-- fontawesome icons -->
        <script src="https://kit.fontawesome.com/a4f543b8bc.js" crossorigin="anonymous"></script>
    </body>
</html>