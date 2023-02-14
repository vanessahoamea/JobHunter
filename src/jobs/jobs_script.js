$(document).ready(function() {
    //load job data
    const params = new Proxy(new URLSearchParams(window.location.search), {
        get: (searchParams, prop) => searchParams.get(prop),
    });
    currentJobId = params.id;

    $.ajax({
        url: "../api/get_job.php",
        method: "GET",
        data: {"id": currentJobId},
        contentType: "application/x-www-form-urlencoded",
        dataType: "json",
        success: function(response) {
            $(".job-data-upper").empty();
            $(".job-data-upper").append("<h2>" + response["title"] + "</h2>");
            $(".job-data-upper").append("<p class='date-posted'>" + response["date_posted"] + "</p>");

            $(".job-data-lower").empty();
            $(".job-data-lower").append("<p><i class='fa-solid fa-building-user fa-fw'></i><a href='../views/companies.php?id=" + response["company_id"] + "' style='font-weight: bold;'>" + response["company_name"] + "</a></p>");
            $(".job-data-lower").append("<p><i class='fa-solid fa-suitcase fa-fw'></i>" + response["type"] + " (" + response["physical"] + ") &#x2022; " + response["level"] + "</p>");
            if(response["salary"] != null)
                $(".job-data-lower").append("<p><i class='fa-solid fa-money-bill-wave fa-fw'></i>" + response["salary"] + " (per month)</p>");
            $(".job-data-lower").append("<p><i class='fa-solid fa-location-dot fa-fw'></i>in " + response["location_name"] + "</p>");

            $("#skills-section").children("div").remove();
            const jobRequirements = $("<div class='job-requirements'></div>");
            JSON.parse(response["skills"]).forEach(skill => {
                jobRequirements.append("<div class='skill-tag'>" + skill + "</div>");
            })
            $("#skills-section").append(jobRequirements);

            $("#description-section").children("div").remove();
            $("#description-section").append(response["description"]);
        }
    });
});

let currentJobId = -1;

//apply for job
$(window).click(function(event) {
    if(event.target == $(".modal")[0])
    toggleModal();
});

function toggleModal()
{
    if($(".modal").eq(0).css("display") == "none")
    {
        $(".modal-wrapper").empty();
        $(".modal-wrapper").append("<p>Would you like to send an application for this job?</p>");
        $(".modal-wrapper").append("<button class='search-button' onclick='apply()'>Apply</button>");

        $(".modal").eq(0).css("display", "block");
        $(".modal-wrapper").css("display", "block");
    }
    else
    {
        $(".modal").eq(0).css("display", "none");
        $(".modal-wrapper").css("display", "none");
    }
}

function apply()
{
    const bearerToken = getCookie("jwt");
    if(bearerToken == "")
    {
        window.location.href = "../login";
        return;
    }

    $.ajax({
        url: "../api/apply.php",
        method: "POST",
        data: JSON.stringify({"job_id": currentJobId}),
        contentType: "application/x-www-form-urlencoded",
        dataType: "json",
        beforeSend: function(xmlhttp) {
            xmlhttp.setRequestHeader("Authorization", "Bearer " + bearerToken);
        },   
        success: function() {
            $(".modal-wrapper").html("<p>Your application was sent.</p>");
        },
        error: function(xmlhttp) {
            $(".modal-wrapper").html("<p>" + JSON.parse(xmlhttp.responseText)["message"] + "</p>");
        },
        complete: function() {
            $(".modal-wrapper").append("<button class='search-button' onclick='toggleModal()'>Close</button>");
        }
    });
}

//helper function
function redirect()
{
    window.location.href = "../applicants?id=" + currentJobId;
}