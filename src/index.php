<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Job Hunter</title>
        <link rel="stylesheet" type="text/css" href="style.css" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">

        <script src="script.js" async></script>
    </head>

    <body>
        <nav id="navbar" class="topnav">
            <a href="#" id="logo">JobHunter</a>
            <a href="search" class="nav-tab">Recent jobs</a>
            <?php if(!isset($_COOKIE["jwt"])): ?>
                <div class="right">
                    <a href="login" class="nav-tab">Login</a>
                    <a href="create-account" class="nav-tab">Create account</a>
                </div>
            <?php else: ?>
                <div class="right">
                    <a href="profile" class="nav-tab">Profile</a>
                    <a href="javascript:void(0)" class="nav-tab" onclick="logout(true)">Log out</a>
                </div>
            <?php endif; ?>
            <a href="javascript:void(0);" class="icon" onclick="expand()">
                <i class="fa fa-bars"></i>
            </a>
        </nav>

        <div id="main">
            <h1>Start your journey here.</h1>

            <input id="search" class="search" type="text" placeholder="Enter keywords"/>
            <button class="search-button">Search</button>
        </div>
        
        <!-- fontawesome icons -->
        <script src="https://kit.fontawesome.com/a4f543b8bc.js" crossorigin="anonymous"></script>
    </body>

    <script>
        function searchKeywords()
        {
            const keywords = encodeURIComponent(document.getElementById("search").value);
            window.location.href = "search?keywords=" + keywords;
        }

        document.querySelector(".search-button").addEventListener("click", () => {
            searchKeywords();
        });

        document.getElementById("search").addEventListener("keyup", (event) => {
            if(event.key == "Enter")
                searchKeywords();
        });
    </script>
</html>