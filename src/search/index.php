<?php
$canApply = false;
if(!isset($_COOKIE["jwt"]))
    $canApply = true;
else
{
    require_once("../controllers/jwt_controller.php");

    $data = JWTController::getPayload($_COOKIE["jwt"]);
    if(JWTController::validateToken($_COOKIE["jwt"]) && $data["account_type"] == "candidate")
        $canApply = true;

    //notifications
    if($data["account_type"] == "company")
    {
        require_once("../models/database.php");
        require_once("../models/company_model.php");
        require_once("../controllers/company_controller.php");

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
        <title>Recent jobs</title>
        <link rel="stylesheet" type="text/css" href="../style.css" />
        <link rel="stylesheet" type="text/css" href="search_style.css" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta charset="UTF-8">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">

        <script src="../script.js" defer></script>
        <script src="search_script.js" defer></script>
        <script src="../util/autocomplete_script.js" defer></script>
        <script src="../util/skill_tags_script.js" defer></script>
    </head>

    <body>
        <nav id="navbar" class="topnav">
            <a href="../" id="logo">JobHunter</a>
            <a href="#" class="nav-tab current-page">Recent jobs</a>
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

        <?php if($canApply): ?>
            <div id="can-apply" style="display: none;"></div>
        <?php endif; ?>

        <div id="main">
            <div id="wrapper">
                <div class="upper-searchbar">
                    <input type="text" id="search" class="search" placeholder="Enter keywords">
                    <button id="search-button" class="search-button">Search</button>
                </div>

                <div class="lower-content">
                    <div class="filters">
                        <h2>Apply filters</h2>

                        <h3>Location</h3>
                        <div>
                            <input type="text" id="location" class="input" style="margin-bottom: 0;">
                            <input type="number" id="location-lat" style="display: none;"></input>
                            <input type="number" id="location-lon" style="display: none;"></input>
                            <div id="listing-container"></div>
                        </div>

                        <h3 style="margin-top: 15px;">Skills</h3>
                        <div class="skill-box">
                            <ul id="skill-list">
                                <input type="text" id="skills" name="skills">
                            </ul>
                        </div>

                        <h3>Job type</h3>
                        <div class="checkboxes">
                            <label for="full-time">
                                <input type="checkbox" id="full-time" name="job-type" value="Full-time"> Full-time
                            </label>
                            <label for="part-time">
                                <input type="checkbox" id="part-time" name="job-type" value="Part-time"> Part-time
                            </label>
                            <label for="internship-type">
                                <input type="checkbox" id="internship-type" name="job-type" value="Internship"> Internship
                            </label>
                        </div>

                        <h3>Level</h3>
                        <div class="checkboxes">
                            <label for="entry-level">
                                <input type="checkbox" id="entry-level" name="level" value="Entry-level"> Entry-level
                            </label>
                            <label for="intermediate">
                                <input type="checkbox" id="intermediate" name="level" value="Intermediate"> Intermediate
                            </label>
                            <label for="mid-level">
                                <input type="checkbox" id="mid-level" name="level" value="Mid-level"> Mid-level
                            </label>
                            <label for="senior-level">
                                <input type="checkbox" id="senior-level" name="level" value="Senior-level"> Senior-level
                            </label>
                            <label for="internship-level">
                                <input type="checkbox" id="internship-level" name="level" value="Internship"> Internship
                            </label>
                        </div>

                        <h3>Others</h3>
                        <div class="checkboxes">
                            <label for="salary">
                                <input type="checkbox" id="salary" name="salary" value="on"> Salary specified
                            </label>
                        </div>
                    </div>

                    <div class="job-postings">
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

            <div id="pagination"></div>
        </div>

        <!-- apply for job modal -->
        <div class="modal">
            <div class="modal-content">
                <span class="close" onclick="getId(null, null)">&times;</span>

                <div class="modal-wrapper" style="text-align: center;">
                    <p>Would you like to send an application for this job?</p>
                    <button class="search-button" onclick="apply()">Apply</button>
                </div>
            </div>
        </div>
        
        <!-- jQuery -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js" crossorigin="anonymous"></script>
        <!-- fontawesome icons -->
        <script src="https://kit.fontawesome.com/a4f543b8bc.js" crossorigin="anonymous"></script>
    </body>
</html>