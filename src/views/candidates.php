<?php
if(!isset($_GET["id"]))
{
    header("location: ..");
    exit();
}

require_once("../models/database.php");
require_once("../models/candidate_model.php");
require_once("../controllers/candidate_controller.php");

$candidate = new CandidateController($_GET["id"]);
$response = $candidate->getCandidateData();
if($response == -1 || $response == 0)
{
    include("page_not_found.php");
    exit();
}
else
    $title = $response["first_name"] . " " . $response["last_name"];

require_once("../controllers/jwt_controller.php");

$selfView = false;
$isCandidate = false;
if(isset($_COOKIE["jwt"]))
{
    $data = JWTController::getPayload($_COOKIE["jwt"]);
    if(JWTController::validateToken($_COOKIE["jwt"]) && $data["account_type"] == "candidate")
    {
        $isCandidate = true;
        if($data["id"] == $_GET["id"])
            $selfView = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <title><?php echo $title; ?></title>
        <link rel="stylesheet" type="text/css" href="../style.css" />
        <link rel="stylesheet" type="text/css" href="../profile/candidate_profile_style.css" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta charset="UTF-8">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">

        <script src="../script.js" defer></script>
        <script src="../profile/candidate_profile_script_init.js" defer></script>
        <script src="../profile/candidate_profile_script.js" defer></script>
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
                    <img class="profile-picture skeleton" src="../assets/default.jpg" alt="Profile picture."></img>
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
                    <div class="edit-button">
                        <button onclick="window.location.href = '../settings'">Edit profile</button>
                    </div>
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
                        <button id="education-button" class="add-button" onclick="showModal(2)">Add new education</button>
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
                        <button id="projects-button" class="add-button" onclick="showModal(3)">Add new project</button>
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
                        <label for="about">Tell us about youself, your skills, etc.</label>
                        <textarea id="about" name="about" class="input" rows=15></textarea>
                    </form>

                    <button class="add-button" onclick="updateAbout()">Update about</button>
                </div>

                <!-- experience modal -->
                <div class="modal-wrapper">
                    <form id="experience-form" action="#">
                        <label for="job-title">Title</label> <span class="warning-text">(this field is required)</span>
                        <input type="text" id="job-title" name="job-title" class="input">

                        <label for="company-name">Company name</label> <span class="warning-text">(this field is required)</span>
                        <input type="text" id="company-name" name="company-name" class="input" style="margin-bottom: 0px;">
                        <input type="number" id="company-id" style="display: none;">
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
                            <input type="checkbox" id="job-ongoing" name="job-ongoing" onclick="toggleEndDate(1, 'job')">
                            <label for="job-ongoing">Ongoing</label>
                        </div>

                        <label for="job-description">Description (optional)</label>
                        <textarea id="job-description" name="job-description" class="input" rows=10></textarea>
                    </form>

                    <button class="add-button" onclick="addExperience()">Add experience</button>
                </div>

                <!-- education modal -->
                <div class="modal-wrapper">
                    <form id="#education-form" action="#">
                        <label for="institution-name">Institution name</label> <span class="warning-text">(this field is required)</span>
                        <input type="text" id="institution-name" name="institution-name" class="input">

                        <div class="dates">
                            <div>
                                <label for="education-start-date">Start date</label>
                                <div name="education-start-date">
                                    <select id="education-start-month" name="education-start-month" class="select">
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
                                <label for="education-end-date">End date</label>
                                <div name="education-end-date">
                                    <select id="education-end-month" name="education-end-month" class="select">
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
                            <input type="checkbox" id="education-ongoing" name="education-ongoing" onclick="toggleEndDate(3, 'education')">
                            <label for="education-ongoing">Ongoing</label>
                        </div>

                        <label for="degree">Degree (optional)</label>
                        <input type="text" id="degree" name="degree" class="input">

                        <label for="study-field">Field of study (optional)</label>
                        <input type="text" id="study-field" name="study-field" class="input">

                        <label for="education-description">Description (optional)</label>
                        <textarea id="education-description" name="education-description" class="input" rows=10></textarea>
                    </form>

                    <button class="add-button" onclick="addEducation()">Add education</button>
                </div>

                <!-- projects modal -->
                <div class="modal-wrapper">
                    <form id="#project-form" action="#">
                        <label for="project-name">Project name</label> <span class="warning-text">(this field is required)</span>
                        <input type="text" id="project-name" name="project-name" class="input">

                        <div class="dates">
                            <div>
                                <label for="project-start-date">Start date</label>
                                <div name="project-start-date">
                                    <select id="project-start-month" name="project-start-month" class="select">
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
                                <label for="project-end-date">End date</label>
                                <div name="project-end-date">
                                    <select id="project-end-month" name="project-end-month" class="select">
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
                            <input type="checkbox" id="project-ongoing" name="project-ongoing" onclick="toggleEndDate(5, 'project')">
                            <label for="project-ongoing">Ongoing</label>
                        </div>

                        <label for="project-link">Link to project (optional)</label>
                        <input type="url" id="project-link" name="project-link" class="input">

                        <label for="project-description">Description (optional)</label>
                        <textarea id="project-description" name="project-description" class="input" rows=10></textarea>
                    </form>

                    <button class="add-button" onclick="addProject()">Add project</button>
                </div>

                <!-- delete item modal -->
                <div class="modal-wrapper delete-modal" style="text-align: center;">
                    <p>Are you sure you want to delete this entry?</p>
                    <button class="add-button" onclick="deleteItem()">Delete</button>
                </div>
            </div>
        </div>

        <!-- jQuery -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js" crossorigin="anonymous"></script>
        <!-- fontawesome icons -->
        <script src="https://kit.fontawesome.com/a4f543b8bc.js" crossorigin="anonymous"></script>
    </body>
</html>