function logout(mainPage = false)
{
    document.cookie = "jwt=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    if(!mainPage)
        window.location.href = "..";
    else
        window.location.href = ".";
}

function expand()
{
    const navbar = document.getElementById("navbar");
    if(navbar.className === "topnav")
        navbar.className += " responsive";
    else
        navbar.className = "topnav";
}