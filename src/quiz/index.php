<?php
$isCandidate = false;
if(isset($_COOKIE["jwt"]))
{
    require_once("../controllers/jwt_controller.php");

    $data = JWTController::getPayload($_COOKIE["jwt"]);
    if(JWTController::validateToken($_COOKIE["jwt"]))
        if($data["account_type"] == "candidate")
            $isCandidate = true;
        else
        {
            require_once("../models/database.php");
            require_once("../models/company_model.php");
            require_once("../controllers/company_controller.php");

            $company = new CompanyController($data["id"]);

            //notifications
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
        <title>Career quiz</title>
        <link rel="stylesheet" type="text/css" href="../style.css" />
        <link rel="stylesheet" type="text/css" href="quiz_style.css" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta charset="UTF-8">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">

        <script src="../script.js" defer></script>
        <script src="quiz_script.js" defer></script>
    </head>

    <body>
        <nav id="navbar" class="topnav">
            <a href="../" id="logo">JobHunter</a>
            <a href="../search" class="nav-tab">Recent jobs</a>
            <div class="right">
                <?php if(isset($_COOKIE["jwt"])): ?>
                    <a href="../profile">Profile</a>
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

        <div id="main">
            <div id="wrapper">
                <form id="form" action="#" method="post">
                    <div id="question1" class="question">
                        <label>What are you best at?</label>
                        <div class="options">
                            <input type="radio" value="3,5" name="question1-option">
                            <div class="option">Problem solving</div>

                            <input type="radio" value="4,5,6" name="question1-option">
                            <div class="option">Helping others</div>

                            <input type="radio" value="0,1,3" name="question1-option">
                            <div class="option">Coming up with creative ideas/solutions</div>

                            <input type="radio" value="2,4" name="question1-option">
                            <div class="option">Communicating with people</div>

                            <input type="radio" value="2,6" name="question1-option">
                            <div class="option">Winning debates</div>

                            <input type="radio" value="0,1" name="question1-option">
                            <div class="option">Being attentive to details</div>
                        </div>
                    </div>

                    <div id="question2" class="question">
                        <label>Are you a sociable person?</label>
                        <div class="options">
                            <input type="radio" value="2,4,5,6" name="question2-option">
                            <div class="option">Yes, I like being around other people</div>

                            <input type="radio" value="0,1,3" name="question2-option">
                            <div class="option">No, I prefer being by myself</div>
                        </div>
                    </div>

                    <div id="question3" class="question">
                        <label>Would you like to pursue a degree in college?</label>
                        <div class="options">
                            <input type="radio" value="0,2,4,5,6" name="question3-option">
                            <div class="option">Yes, I want to continue my studies and get a degree</div>

                            <input type="radio" value="1,3" name="question3-option">
                            <div class="option">No, I don't want to pursue a degree</div>
                        </div>
                    </div>

                    <div id="question4" class="question">
                        <label>How do you want to change the world?</label>
                        <div class="options">
                            <input type="radio" value="5,6" name="question4-option">
                            <div class="option">Offering help to those who need it</div>

                            <input type="radio" value="1,6" name="question4-option">
                            <div class="option">Representing the under-appreciated</div>

                            <input type="radio" value="1,4,5" name="question4-option">
                            <div class="option">Making sure that people are happy and content</div>

                            <input type="radio" value="0,2,3" name="question4-option">
                            <div class="option">Making life easier for others</div>

                            <input type="radio" value="3,4" name="question4-option">
                            <div class="option">Educating the next generation</div>

                            <input type="radio" value="2" name="question4-option">
                            <div class="option">Donating to charities</div>
                        </div>
                    </div>

                    <div id="question5" class="question">
                        <label>Which of these professions appeals the most to you?</label>
                        <div class="options">
                            <input type="radio" value="4" name="question5-option">
                            <div class="option">Special education teacher</div>

                            <input type="radio" value="2" name="question5-option">
                            <div class="option">Data scientist</div>

                            <input type="radio" value="0" name="question5-option">
                            <div class="option">Civil engineer</div>

                            <input type="radio" value="1" name="question5-option">
                            <div class="option">Graphic designer</div>

                            <input type="radio" value="5" name="question5-option">
                            <div class="option">Veterinarian</div>

                            <input type="radio" value="6" name="question5-option">
                            <div class="option">Notary</div>

                            <input type="radio" value="3" name="question5-option">
                            <div class="option">Software engineer</div>
                        </div>
                    </div>

                    <div id="results"></div>

                    <div class="buttons-container">
                        <button type="button" class="form-button" onclick="showNext()">Next</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- jQuery -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js" crossorigin="anonymous"></script>
        <!-- fontawesome icons -->
        <script src="https://kit.fontawesome.com/a4f543b8bc.js" crossorigin="anonymous"></script>
    </body>
</html>