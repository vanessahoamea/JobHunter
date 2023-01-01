<?php
if(!isset($_COOKIE["jwt"]))
{
    header("location: ../login");
    exit();
}

require_once("../controllers/jwt_controller.php");

$data = JWTController::getPayload($_COOKIE["jwt"]);
if(!isset($data["account_type"]) || $data["account_type"] != "company")
{
    header("location: ../");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Post job</title>
        <link rel="stylesheet" type="text/css" href="../style.css" />
        <link rel="stylesheet" type="text/css" href="post_job_style.css" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta charset="UTF-8">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">

        <script src="../script.js" async></script>
        <script src="post_job_script.js" defer></script>
        <!-- fontawesome icons -->
        <script src="https://kit.fontawesome.com/a4f543b8bc.js" crossorigin="anonymous"></script>
    </head>

    <body>
        <nav id="navbar" class="topnav">
            <a href="../" id="logo">JobHunter</a>
            <a href="#" class="nav-tab">Recent jobs</a>
            <div class="right">
                <a href="../profile">Profile</a>
                <a href="javascript:void(0)" class="nav-tab" onclick="logout()">Log out</a>
            </div>
            <a href="javascript:void(0);" class="icon" onclick="expand()">
                <i class="fa fa-bars"></i>
            </a>
        </nav>

        <div id="main">
            <h1 id="top-text">Let people know you are hiring.</h1>

            <div id="wrapper">
                <form id="form" action="#" method="post">
                    <div class="pair">
                        <div style="text-align: left;">
                            <label for="title">Job title</label>
                            <span class="warning-text">(this field is required)</span>
                        </div>
                        <input type="text" id="title" name="title" class="input">
                    </div>

                    <div class="pair">
                        <label for="skills">Skills required (separated by commas)</label>
                        <div class="skill-box">
                            <ul id="skill-list">
                                <input type="text" id="skills" name="skills">
                            </ul>
                        </div>
                    </div>

                    <div class="pair">
                        <label for="type">Job type</label>
                        <select id="type" name="type" class="select">
                            <option value="Full-time">Full-time</option>
                            <option value="Part-time">Part-time</option>
                            <option value="Internship">Internship</option>
                            <option value="Self-employed">Self-employed</option>
                            <option value="Freelance">Freelance</option>
                        </select>
                    </div>

                    <div class="pair">
                        <label for="level">Level</label>
                        <select id="level" name="level" class="select">
                            <option value="Entry-level">Entry-level</option>
                            <option value="Intermediate">Intermediate</option>
                            <option value="Mid-level">Mid-level</option>
                            <option value="Senior-level">Senior-level</option>
                            <option value="Internship">Internship</option>
                        </select>
                    </div>

                    <div class="pair">
                        <div style="text-align: left;">
                            <label for="location">Location</label>
                            <span class="warning-text">(select a location from the list)</span>
                        </div>
                        <div>
                            <input type="text" id="location" name="location" class="input" style="margin-bottom: 0;">
                            <input type="number" id="location-lat" style="display: none;"></input>
                            <input type="number" id="location-lon" style="display: none;"></input>
                            <div id="listing-container"></div>
                        </div>
                    </div>

                    <div class="pair">
                        <label for="salary" style="margin-top: 15px;">Salary</label>
                        <input type="text" id="salary" name="salary" class="input">
                    </div>

                    <div class="radio-buttons">
                        <label>
                            <input type="radio" id="on-site" name="physical" value="On-site" checked>
                            On-site
                        </label>

                        <label>
                            <input type="radio" id="remote" name="physical" value="Remote">
                            Remote
                        </label>

                        <label>
                            <input type="radio" id="hybrid" name="physical" value="Hybrid">
                            Hybrid
                        </label>
                    </div>

                    <div class="pair">
                        <div style="text-align: left;">
                            <label for="description">Job description</label>
                            <span class="warning-text">(this field is required)</span>
                        </div>
                        <div>
                            <div class="text-options">
                                <button type="button" id="bold" class="option-button">
                                    <i class="fa-solid fa-bold"></i>
                                </button>

                                <button type="button" id="italic" class="option-button">
                                    <i class="fa-solid fa-italic"></i>
                                </button>

                                <button type="button" id="underline" class="option-button">
                                    <i class="fa-solid fa-underline"></i>
                                </button>

                                <button type="button" id="strikethrough" class="option-button">
                                    <i class="fa-solid fa-strikethrough"></i>
                                </button>

                                <button type="button" id="insertUnorderedList" class="option-button list-option">
                                    <i class="fa-solid fa-list-ul"></i>
                                </button>

                                <button type="button" id="insertOrderedList" class="option-button list-option">
                                    <i class="fa-solid fa-list-ol"></i>
                                </button>
                            </div>
                            <div id="description" name="description" class="input" contenteditable="true"></div>
                        </div>
                    </div>
                </form>

                <div class="button-container">
                    <button class="submit-button" onclick="checkValues()">Post</button>
                </div>
            </div>

            <div id="success">
                <h1>Job posted.</h1>
                <a href="#" onclick="location.reload()">Post another one?</a>
            </div>
        </div>
        
        <!-- jQuery -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js" crossorigin="anonymous"></script>
    </body>
</html>