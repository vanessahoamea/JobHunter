<?php
require_once("../controllers/jwt_controller.php");

if(!isset($_COOKIE["jwt"]) || !JWTController::validateToken($_COOKIE["jwt"]))
{
    header("location: ..");
    exit();
}

$isCandidate = false;
$data = JWTController::getPayload($_COOKIE["jwt"]);
if($data["account_type"] == "candidate")
    $isCandidate = true;
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Settings</title>
        <link rel="stylesheet" type="text/css" href="../style.css" />
        <link rel="stylesheet" type="text/css" href="settings_style.css" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta charset="UTF-8">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">

        <script src="../script.js" defer></script>
        <script src="settings_script.js" defer></script>
    </head>

    <body>
        <nav id="navbar" class="topnav">
            <a href="../" id="logo">JobHunter</a>
            <a href="../search" class="nav-tab">Recent jobs</a>
            <div class="right">
                <a href="../profile">Profile</a>
                <?php if($isCandidate): ?>
                    <a href="../my-jobs" class="nav-tab">My jobs</a>
                <?php endif; ?>
                <a href="javascript:void(0)" class="nav-tab" onclick="logout()">Log out</a>
            </div>
            <a href="javascript:void(0);" class="icon" onclick="expand()">
                <i class="fa fa-bars"></i>
            </a>
        </nav>

        <div id="main">
            <div id="wrapper">
                <div class="profile-picture">
                    <img id="profile-picture" src="../assets/default.jpg" alt="Profile picture"></img>
                    <input type="file" id="image-upload" name="image-upload" accept="image/png, image/jpeg" onclick="this.value = null" style="display: none;">
                    <label for="image-upload" id="image-upload-label">Change picture</label>
                </div>

                <div class="ahh">
                <a class="remove-text" onclick="removePicture()">Remove profile picture</a>
                </div>

                <?php if($isCandidate): ?>
                    <!-- candidate data -->
                    <form id="candidate-form" action="#" method="post">
                        <div class="pair">
                            <label for="candidate-fname">First name</label>
                            <input type="text" id="candidate-fname" name="candidate-fname" class="input">
                        </div>

                        <div class="pair">
                            <label for="candidate-lname">Last name</label>
                            <input type="text" id="candidate-lname" name="candidate-lname" class="input">
                        </div>

                        <div class="pair">
                            <label for="candidate-email">E-mail</label>
                            <input type="text" id="candidate-email" name="candidate-email" class="input">
                        </div>

                        <div class="pair">
                            <label for="candidate-phone">Phone number</label>
                            <input type="text" id="candidate-phone" name="candidate-phone" class="input">
                        </div>

                        <div class="pair">
                            <label for="candidate-location">Location</label>
                            <input type="text" id="candidate-location" name="candidate-location" class="input">
                        </div>

                        <div class="pair">
                            <label for="candidate-password">Change password</label>
                            <input type="password" id="candidate-password" name="candidate-password" class="input">
                        </div>
                    </form>

                    <div class="button-container">
                        <button class="candidate-button" onclick="toggleModal()">Save changes</button>
                    </div>
                <?php else: ?>
                    <!-- company data -->
                    <form id="company-form" action="#" method="post">
                        <div class="pair">
                            <label for="company-name">Company name</label>
                            <input type="text" id="company-name" name="company-name" class="input">
                        </div>

                        <div class="pair">
                            <label for="company-email">E-mail</label>
                            <input type="text" id="company-email" name="company-email" class="input">
                        </div>

                        <div class="pair">
                            <label for="company-address">Address</label>
                            <input type="text" id="company-address" name="company-address" class="input">
                        </div>

                        <div class="pair">
                            <label for="company-website">Website</label>
                            <input type="text" id="company-website" name="company-website" class="input">
                        </div>
                        
                        <div class="pair">
                            <label for="company-password">Change password</label>
                            <input type="password" id="company-password" name="company-password" class="input">
                        </div>
                    </form>

                    <div class="button-container">
                        <button class="candidate-button company-button" onclick="toggleModal()">Save changes</button>
                    </div>
                <?php endif; ?>
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
        <!-- Cropper.js -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js" integrity="sha512-6lplKUSl86rUVprDIjiW8DuOniNX8UDoRATqZSds/7t6zCQZfaCe3e5zcGaQwxa8Kpn5RTM9Fvl3X2lLV4grPQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" integrity="sha512-cyzxRvewl+FOKTtpBzYjW6x6IAYUCZy3sGP40hn+DQkqeluGRCax7qztK2ImL64SA+C7kVWdLI6wvdlStawhyw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <!-- fontawesome icons -->
        <script src="https://kit.fontawesome.com/a4f543b8bc.js" crossorigin="anonymous"></script>
    </body>
</html>