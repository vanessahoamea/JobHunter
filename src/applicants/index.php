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
    if(!$company->validate($_GET["id"], null, "jobs"))
    {
        header("location: ../");
        exit();
    }
}

//notifications
$notifications = $company->getNotificationCount();
if($notifications == 0)
    $notifications = 0;
else
    $notifications = min(99, $notifications["unread_notifications"]);
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
            <div class="right">
                <a href="../profile" class="nav-tab">Profile</a>
                <a href="../notifications">Notifications
                    <?php if($notifications > 0): ?>
                        <div class="notifs"><?php echo $notifications; ?></div>
                    <?php endif; ?>
                </a>
                <a href="javascript:void(0)" class="nav-tab" onclick="logout(false)">Log out</a>
            </div>
            <a href="javascript:void(0);" class="icon" onclick="expand()">
                <i class="fa fa-bars"></i>
            </a>
        </nav>

        <div id="main">
            <div class="filters">
                <h2>Filter candidates</h2>

                <h3>Minimum years of experience</h3>
                <label for="minimum-years">
                    <input class="input" type="number" id="minimum-years" name="minimum-years" min="0" value="0">
                </label>

                <h3>Others</h3>
                <label for="sort-experience">
                    <input type="checkbox" id="sort-experience" name="sort-experience"> Sort by amount of experience
                </label>
                <label for="sort-relevance">
                    <input type="checkbox" id="sort-relevance" name="sort-relevance"> Sort by amount of experience relevant to this position
                </label>
                <label for="job-hoppers">
                    <input type="checkbox" id="job-hoppers" name="job-hoppers"> Hide candidates that might be job hoppers
                </label>
                <label for="descriptions">
                    <input type="checkbox" id="descriptions" name="descriptions"> Hide candidates that don't provide descriptions for previous roles
                </label>
                <label for="show-ignored">
                    <input type="checkbox" id="show-ignored" name="show-ignored"> Show ignored candidates
                </label>

                <div class="buttons" style="margin-top: 10px;">
                    <button class="search-button" onclick="applyFilters(<?php echo $_GET['id'] ?>)">Apply</button>
                </div>
            </div>

            <div id="wrapper">
                <div class="candidates-list">
                    <table>
                        <tr>
                            <th>Candidate</th>
                            <th>Longest held positions</th>
                            <th>Insights</th>
                            <th>Actions</th>
                        </tr>

                        <tr class="filler-row">
                            <td colspan="4">
                                <div class="skeleton skeleton-text"></div>
                                <div class="skeleton skeleton-text"></div>
                            </td>
                        </tr>

                        <tr class="filler-row">
                            <td colspan="4">
                                <div class="skeleton skeleton-text"></div>
                                <div class="skeleton skeleton-text"></div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- modal -->
        <div class="modal">
            <div class="modal-content">
                <span class="close" onclick="toggleModal(null, null, null)">&times;</span>

                <div class="modal-wrapper" style="text-align: center;"></div>
            </div>
        </div>
        
        <!-- jQuery -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js" crossorigin="anonymous"></script>
        <!-- fontawesome icons -->
        <script src="https://kit.fontawesome.com/a4f543b8bc.js" crossorigin="anonymous"></script>
    </body>
</html>