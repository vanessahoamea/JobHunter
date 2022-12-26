$(document).ready(function() {
    //replace placeholder skeleton with text and images
    const params = new Proxy(new URLSearchParams(window.location.search), {
        get: (searchParams, prop) => searchParams.get(prop),
    });
    const id = params.id;
    
    //profile section
    $.ajax({
        url: "../api/get_company.php",
        method: "GET",
        data: {"id": id},
        contentType: "application/x-www-form-urlencoded",
        dataType: "json",
        success: function(response) {
            $("#company-name").text(response["company_name"]);

            $(".information-list").empty();
            if(response["address"] != null)
                $(".information-list").append("<li><i class='fa-solid fa-building'></i>&ensp;" + response["address"] + "</li>");
            if(response["website"] != null)
                $(".information-list").append("<li><i class='fa-solid fa-globe'></i> <a href='" + response["website"] + "'>" + response["website"] + "</a></li>");
        }
    });
});

// function show_data()
// {
//     let bearerToken = getCookie("jwt");

//     let xmlhttp = new XMLHttpRequest();
//     xmlhttp.open("GET", "../api/get_experience_data.php", true);
//     xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
//     xmlhttp.setRequestHeader("Authorization", "Bearer " + bearerToken);
//     xmlhttp.responseType = "json";

//     xmlhttp.onload = function(e)
//     {
//         if(this.status == 200)
//         {
//             string = "Has a total of " + Math.floor(this.response["total_months"] / 12) + " years and " + (this.response["total_months"] % 12) + " months of work experience<br>";
//             if(this.response["average_employment_period"] >= 6)
//                 string += "Is likely to stay at one workplace for a longer period of time<br>";
//             else
//                 string += "Not likely to stay at one workplace for long periods of time, might be a job hopper<br>";
//             if(this.response["adds_descriptions"])
//                 string += "Provides descriptions for most of their previous roles<br>";
//             else
//                 string += "Doesn't provide descriptions for most of their previous roles<br>";

//             $("#result-text").html(string);
//         }
//     };
//     xmlhttp.send();
// }