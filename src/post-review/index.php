<?php
if(!isset($_COOKIE["jwt"]))
{
    header("location: ../login");
    exit();
}

require_once("../controllers/jwt_controller.php");

$data = JWTController::getPayload($_COOKIE["jwt"]);
if(!isset($_GET["id"]) || !JWTController::validateToken($_COOKIE["jwt"]) || $data["account_type"] != "candidate")
{
    header("location: ../");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Post review</title>
        <link rel="stylesheet" type="text/css" href="../style.css" />
        <link rel="stylesheet" type="text/css" href="post_review_style.css" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta charset="UTF-8">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">

        <script src="../script.js" defer></script>
        <script src="post_review_script.js" defer></script>
    </head>

    <body>
        <nav id="navbar" class="topnav">
            <a href="../" id="logo">JobHunter</a>
            <a href="../search" class="nav-tab">Recent jobs</a>
            <div class="right">
                <?php if(isset($_COOKIE["jwt"])): ?>
                    <a href="../profile">Profile</a>
                    <a href="../my-jobs" class="nav-tab">My jobs</a>
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
            <div id="wrapper">
                <form id="form" action="#" method="post">
                    <div class="pair">
                        <div>
                            <label for="title">Job title</label>
                            <span class="warning-text">(this field is required)</span>
                        </div>
                        <input type="text" id="title" name="title" class="input">
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
                        <label>Your employment status at this company</label>
                        <div class="radio-buttons">
                            <label>
                                <input type="radio" id="current" name="status" value="current" checked>
                                Current employee
                            </label>

                            <label>
                                <input type="radio" id="former" name="status" value="former">
                                Former employee
                            </label>
                        </div>
                    </div>

                    <div class="pair">
                        <div>
                            <label for="pros">The company's good points</label>
                            <span class="warning-text">(this field is required)</span>
                        </div>
                        <textarea id="pros" name="pros" class="input" rows=5></textarea>
                    </div>

                    <div class="pair">
                        <div>
                            <label for="cons">The company's bad points</label>
                            <span class="warning-text">(this field is required)</span>
                        </div>
                        <textarea id="cons" name="cons" class="input" rows=5></textarea>
                    </div>

                    <div class="pair">
                        <div>
                            <label>Your overall rating</label>
                            <span class="warning-text">(please select a rating)</span>
                        </div>
                        <div class="stars-container">
                            <div class="star-widget">
                                <input type="radio" name="rating" id="rate-5" value="5">
                                <label for="rate-5" class="fa-solid fa-star"></label>
                                <input type="radio" name="rating" id="rate-4" value="4">
                                <label for="rate-4" class="fa-solid fa-star"></label>
                                <input type="radio" name="rating" id="rate-3" value="3">
                                <label for="rate-3" class="fa-solid fa-star"></label>
                                <input type="radio" name="rating" id="rate-2" value="2">
                                <label for="rate-2" class="fa-solid fa-star"></label>
                                <input type="radio" name="rating" id="rate-1" value="1">
                                <label for="rate-1" class="fa-solid fa-star"></label>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="button-container">
                    <button class="submit-button" onclick="checkValues()">Submit review</button>
                </div>
            </div>
        </div>
        
        <!-- jQuery -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js" crossorigin="anonymous"></script>
        <!-- fontawesome icons -->
        <script src="https://kit.fontawesome.com/a4f543b8bc.js" crossorigin="anonymous"></script>
    </body>
</html>