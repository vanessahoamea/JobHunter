<?php
if(!isset($_GET["id"]))
{
    header("location: ..");
    exit();
}

require_once("../models/database.php");
require_once("../models/candidate_model.php");
require_once("../controllers/candidate_controller.php");

$candidate = new CandidateController($_GET["id"], '', '', '', '', '', '', '', '', '');
$response = $candidate->getCandidateData();
if($response == -1 || $response == 0)
{
    include("page_not_found.php");
    exit();
}

require_once("../controllers/jwt_controller.php");

$selfView = false;
if(isset($_COOKIE["jwt"]))
{
    $data = JWTController::getPayload($_COOKIE["jwt"]);
    if(isset($data["id"]) && isset($data["account_type"]) && $data["id"] == $_GET["id"] && $data["account_type"] == "candidate")
        $selfView = true;
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Profile</title>
        <link rel="stylesheet" type="text/css" href="../style.css" />
        <link rel="stylesheet" type="text/css" href="../profile/candidate_profile_style.css" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">

        <script src="../script.js" async></script>
        <script src="../profile/candidate_profile_script.js" async></script>
        <!-- jQuery -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js" crossorigin="anonymous"></script>
        <!-- fontawesome icons -->
        <script src="https://kit.fontawesome.com/a4f543b8bc.js" crossorigin="anonymous"></script>
    </head>

    <body>
        <nav id="navbar" class="topnav">
            <a href="../" id="logo">JobHunter</a>
            <a href="#" class="nav-tab">Recent jobs</a>
            <div class="right">
                <?php if(isset($_COOKIE["jwt"])): ?>
                    <?php if($selfView): ?>
                        <a href="#" class="current-page">Profile</a>
                    <?php else: ?>
                        <a href="../profile">Profile</a>
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
            <div class="upper-container">
                <div class="left-side">
                <div class="profile-picture">
                    <img class="profile-picture skeleton" src="../default.jpg" alt="Profile picture."></img>
                </div>
                    <div class="profile-information">
                        <h1 id="full-name"><div class="skeleton skeleton-text-single" style="height: 30px;"></div></h1>
                        <div class="information-list">
                            <div class="skeleton skeleton-text"></div>
                            <div class="skeleton skeleton-text"></div>
                            <div class="skeleton skeleton-text"></div>
                        </div>
                    </div>
                </div>
                <?php if($selfView): ?>
                    <div class="edit-button"><button>Edit profile</button></div>
                <?php endif; ?>
            </div>

            <div class="lower-container">
                <div class="section-title">
                    <h1>About</h1>
                </div>
                <div class="section-info">
                    <div id="about-content" class="section-content">
                        <div class="skeleton skeleton-text"></div>
                        <div class="skeleton skeleton-text"></div>
                        <div class="skeleton skeleton-text"></div>
                    </div>

                    <?php if($selfView): ?>
                        <button id="about-button" class="add-button" onclick="showModal(0)">Edit about</button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="lower-container">
                <div class="section-title">
                    <h1>Experience</h1>
                </div>
                <div class="section-info">
                    <div id="experience-content" class="section-content">
                        <div class="skeleton skeleton-text"></div>
                        <div class="skeleton skeleton-text"></div>
                        <div class="skeleton skeleton-text"></div>
                    </div>

                    <?php if($selfView): ?>
                        <button id="experience-button" class="add-button" onclick="showModal(1)">Add new experience</button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="lower-container">
                <div class="section-title">
                    <h1>Education</h1>
                </div>
                <div class="section-info">
                    <div id="education-content" class="section-content">
                        <div class="skeleton skeleton-text"></div>
                        <div class="skeleton skeleton-text"></div>
                        <div class="skeleton skeleton-text"></div>
                    </div>

                    <?php if($selfView): ?>
                        <button id="education-button" class="add-button">Add new education</button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="lower-container">
                <div class="section-title">
                    <h1>Projects</h1>
                </div>
                <div class="section-info">
                    <div id="projects-content" class="section-content">
                        <div class="skeleton skeleton-text"></div>
                        <div class="skeleton skeleton-text"></div>
                        <div class="skeleton skeleton-text"></div>
                    </div>

                    <?php if($selfView): ?>
                        <button id="projects-button" class="add-button">Add new project</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- modals for each section -->
        <div class="modal">
            <div class="modal-content">
                <span class="close" onclick="hideModal()">&times;</span>

                <!-- about modal -->
                <div class="modal-wrapper">
                    <form style="width: 95%;">
                        <label for="about">Tell us about youself</label>
                        <textarea id="about" name="about" class="input" rows=15></textarea>
                    </form>

                    <button class="add-button" onclick="javascript:void(0)">Update about</button>
                </div>

                <!-- experience modal -->
                <div class="modal-wrapper">
                    <form id="experience-form" action="#">
                        <label for="job-title">Title</label> <span class="warning-text">(this field is required)</span>
                        <input type="text" id="job-title" name="job-title" class="input">

                        <label for="company-name">Company name</label> <span class="warning-text">(this field is required)</span>
                        <input type="text" id="company-name" name="company-name" class="input" style="margin-bottom: 0px;">
                        <input type="number" id="company-id" value="-1" style="display: none;">
                        <div id="listing-container"></div>

                        <div class="select-fields">
                            <label for="job-type" style="margin-top: 15px;">Job type</label>
                            <select id="job-type" name="job-type" class="select">
                                <option value="Full-time">Full-time</option>
                                <option value="Part-time">Part-time</option>
                                <option value="Internship">Internship</option>
                                <option value="Self-employed">Self-employed</option>
                                <option value="Freelance">Freelance</option>
                            </select>
                        </div>

                        <div class="dates">
                            <div>
                                <label for="job-start-date">Start date</label>
                                <div name="job-start-date">
                                    <select id="job-start-month" name="job-start-month" class="select">
                                        <option value="January">January</option>
                                        <option value="February">February</option>
                                        <option value="March">March</option>
                                        <option value="April">April</option>
                                        <option value="May">May</option>
                                        <option value="June">June</option>
                                        <option value="July">July</option>
                                        <option value="August">August</option>
                                        <option value="September">September</option>
                                        <option value="October">October</option>
                                        <option value="November">November</option>
                                        <option value="December">December</option>
                                    </select>
                                    <select class="year-list select"></select>
                                </div>
                            </div>

                            <div>
                                <label for="job-end-date">End date</label>
                                <div name="job-end-date">
                                    <select id="job-end-month" name="job-end-month" class="select">
                                        <option value="January">January</option>
                                        <option value="February">February</option>
                                        <option value="March">March</option>
                                        <option value="April">April</option>
                                        <option value="May">May</option>
                                        <option value="June">June</option>
                                        <option value="July">July</option>
                                        <option value="August">August</option>
                                        <option value="September">September</option>
                                        <option value="October">October</option>
                                        <option value="November">November</option>
                                        <option value="December">December</option>
                                    </select>
                                    <select class="year-list select"></select>
                                </div>
                            </div>
                        </div>

                        <div style="margin-bottom: 15px;">
                            <input type="checkbox" id="ongoing" name="ongoing" onclick="toggleEndDate()">
                            <label for="ongoing">Ongoing</label>
                        </div>

                        <label for="job-description">Description (optional)</label>
                        <textarea id="job-description" name="job-description" class="input" rows=10></textarea>
                    </form>

                    <button class="add-button" onclick="addExperience()">Add experience</button>
                </div>
            </div>
        </div>
    </body>
</html>