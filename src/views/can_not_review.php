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
                <a href="../profile">Profile</a>
                <a href="../my-jobs" class="nav-tab">My jobs</a>
                <a href="../my-reviews" class="nav-tab">My reviews</a>
                <a href="javascript:void(0)" class="nav-tab" onclick="logout()">Log out</a>
            </div>
            <a href="javascript:void(0);" class="icon" onclick="expand()">
                <i class="fa fa-bars"></i>
            </a>
        </nav>

        <div id="main">
            <button class="return-button" onclick="redirect(<?php echo $_GET['id'] ?>)">Back to company page</button>
            
            <h1>You can not review this company.</h1>
            <p>You must have some past experience at a company to be able to leave a review.</p>
            <p>Make sure the experience section on your profile is complete.</p>
        </div>
        
        <!-- jQuery -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js" crossorigin="anonymous"></script>
        <!-- fontawesome icons -->
        <script src="https://kit.fontawesome.com/a4f543b8bc.js" crossorigin="anonymous"></script>
    </body>

    <style>
        #main p {
            margin: 0;
        }
        .return-button {
            min-height: 8vh;
            min-width: 10vw;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            background-color: black;
            color: white;
            margin-bottom: 20px;
        }
    </style>
</html>