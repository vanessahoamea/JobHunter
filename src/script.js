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

function getCookie(name)
{
    name += "=";
    let decodedCookie = decodeURIComponent(document.cookie);
    let cookies = decodedCookie.split(";");

    for(let i=0; i<cookies.length; i++)
    {
        let cookie = cookies[i];
        while(cookie.charAt(0) == " ")
            cookie = cookie.substring(1);

        if(cookie.indexOf(name) == 0)
            return cookie.substring(name.length, cookie.length);
    }

    return "";
}