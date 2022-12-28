<?php
if(isset($_COOKIE["jwt"]))
{
    header("location: ../profile");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Create account</title>
        <link rel="stylesheet" type="text/css" href="../style.css" />
        <link rel="stylesheet" type="text/css" href="signup_style.css" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">

        <script src="../script.js" async></script>
        <script src="signup_script.js" defer></script>
        <!-- fontawesome icons -->
        <script src="https://kit.fontawesome.com/a4f543b8bc.js" crossorigin="anonymous"></script>
    </head>

    <body>
        <nav id="navbar" class="topnav">
            <a href=".." id="logo">JobHunter</a>
            <a href="#" class="nav-tab">Recent jobs</a>
            <div class="right">
                <a href="../login" class="nav-tab">Login</a>
                <a href="#" class="current-page">Create account</a>
            </div>
            <a href="javascript:void(0);" class="icon" onclick="expand()">
                <i class="fa fa-bars"></i>
            </a>
        </nav>

        <div id="main">
            <h1 id="choose-text">Choose the type of account you want to register as:</h1>

            <!-- default view -->
            <div id="signup-boxes">
                <button class="signup-box" id="candidate-button" onclick="showForm(0)">
                    <h2>Candidate</h2>
                    <p>Easily apply to jobs and get recommendations based on your resume and activity</p>
                </button>
                <button class="signup-box" id="company-button" onclick="showForm(1)">
                    <h2>Company</h2>
                    <p>Post your job listings and have easy access to every candidate and their CVs</p>
                </button>
            </div>

            <!-- signup forms -->
            <button id="return-button" onclick="showBoxes()">Return to selection</button>

            <div id="signup-form">
                <p id="result-text"></p>

                <form id="candidate-form" action="#" method="post">
                    <div>
                        <label for="fname">First name</label>
                    </div>
                    <input type="text" id="fname" name="fname" class="input">

                    <div>
                        <label for="lname">Last name</label>
                    </div>
                    <input type="text" id="lname" name="lname" class="input">

                    <div>
                        <label for="email">E-mail address</label>
                    </div>
                    <input type="text" id="email" name="email" class="input">

                    <label for="phone">Phone number (optional)</label>
                    <input type="tel" id="phone" name="phone" class="input">

                    <div>
                        <label for="password">Password</label>
                    </div>
                    <input type="password" id="password" name="password" class="input">
                </form>

                <form id="company-form" action="#" method="post">
                    <div>
                        <label for="cname">Company name</label>
                    </div>
                    <input type="text" id="cname" name="cname" class="input">

                    <div>
                        <label for="cemail">E-mail address</label>
                    </div>
                    <input type="text" id="cemail" name="cemail" class="input">

                    <label for="address">Address (optional)</label>
                    <input type="text" id="address" name="address" class="input">

                    <div>
                        <label for="cpassword">Password</label>
                    </div>
                    <input type="password" id="cpassword" name="cpassword" class="input">
                </form>

                <div id="button-container">
                    <button id="submit-button" class="candidate-button" onclick="checkValues()">Create account</button>
                    
                    <p>Already have an account? <a href="../login">Log in</a></p>
                </div>
            </div>
        </div>

        <!-- jQuery -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js" crossorigin="anonymous"></script>
    </body>
</html>