<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Error 404</title>
        <link rel="stylesheet" type="text/css" href="http://localhost/Licenta/style.css" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta charset="UTF-8">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">

        <script src="http://localhost/Licenta/script.js" async></script>
    </head>

    <body>
        <div id="main" >
            <h1 style="font-size: 4em; letter-spacing: 3px;">Oops.</h1>
            <h3 style="text-transform: uppercase;">404 - Page not found</h3>
            <p class="p-text">Sorry, we could not find the page you were looking for. Make sure you followed a correct link.</p>
            <button class="return-button" onclick="window.location = 'http://localhost/Licenta'">Go to homepage</button>
        </div>
    </body>

    <style>
        .return-button {
            min-height: 8vh;
            min-width: 10vw;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            background-color: black;
            color: white;
        }

        .p-text {
            width: 40vw;
            margin-left: auto;
            margin-right: auto;
        }
    </style>
</html>