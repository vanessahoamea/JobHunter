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
            $(".company-picture").eq(1).attr("src", response["profile_picture"]);
            $(".job-data-upper").append("<h2>" + response["title"] + "</h2>");
            $(".job-data-upper").append("<p class='date-posted'>" + response["date_posted"] + "</p>");

            $(".job-data-lower").empty();
            $(".job-data-lower").append("<p><i class='fa-solid fa-building-user fa-fw'></i><a href='../views/companies.php?id=" + response["company_id"] + "' style='font-weight: bold;'>" + response["company_name"] + "</a></p>");
            $(".job-data-lower").append("<p><i class='fa-solid fa-suitcase fa-fw'></i>" + response["type"] + " (" + response["physical"] + ") &#x2022; " + response["level"] + "</p>");
            if(response["salary"] != null)
                $(".job-data-lower").append("<p><i class='fa-solid fa-money-bill-wave fa-fw'></i>" + response["salary"] + " (per month)</p>");
            $(".job-data-lower").append("<p><i class='fa-solid fa-location-dot fa-fw'></i>in " + response["location_name"] + "</p>");

            $("#skills-section").children("div").remove();
            if(response["skills"] != "[]")
            {
                const jobRequirements = $("<div class='job-requirements'></div>");
                JSON.parse(response["skills"]).forEach(skill => {
                    jobRequirements.append("<div class='skill-tag'>" + skill + "</div>");
                })
                $("#skills-section").append(jobRequirements);
            }
            else
                $("#skills-section").remove();

            $("#description-section").children("div").remove();
            $("#description-section").append(response["description"]);

            for(let i=1; i<=3; i++)
                questions.push(response["question" + i]);
        }
    });
});

let currentJobId = -1;
let questions = [];

//apply for job
$(window).click(function(event) {
    if(event.target == $(".modal")[0])
    toggleModal(null);
});

function toggleModal(index)
{
    if(index == 1 && questions.filter((question) => question != null).length > 0)
    {
        if($(".modal").eq(0).css("display") == "none")
        {
            $(".modal-wrapper").empty();
            questions.forEach((question, index) => {
                if(question == null)
                    return;

                const div = $("<div style='text-align: left;'></div>");
                div.append(`<label for='question${index + 1}'>${question}</label>`);
                div.append(` <span class='warning-text'>(this field is required)</span>`);
                $(".modal-wrapper").append(div);
                $(".modal-wrapper").append(`<textarea name='question${index + 1}' id='question${index + 1}' class='input' rows=3>`);
            });
            $(".modal-wrapper").append(`<button class='search-button' onclick='checkAnswers()'>Apply</button>`);

            $(".modal").eq(0).css("display", "block");
            $(".modal-wrapper").css("display", "block");
        }
        else
        {
            $(".modal").eq(0).css("display", "none");
            $(".modal-wrapper").css("display", "none");
        }

        return;
    }

    let text = (index == 1) ? "<p>Would you like to send an application for this job?</p>"
               : (index == 2) ? "<p>Would you like to save this job for later?</p>"
               : "<p>Would you like to hide this job posting?</p>";

    if($(".modal").eq(0).css("display") == "none")
    {
        $(".modal-wrapper").empty();
        $(".modal-wrapper").append(text);
        $(".modal-wrapper").append(`<button class='search-button' onclick='postRequest(${index})'>Confirm</button>`);

        $(".modal").eq(0).css("display", "block");
        $(".modal-wrapper").css("display", "block");
    }
    else
    {
        $(".modal").eq(0).css("display", "none");
        $(".modal-wrapper").css("display", "none");
    }
}

function postRequest(index)
{
    const bearerToken = getCookie("jwt");
    if(bearerToken == "")
    {
        window.location.href = "../login";
        return;
    }

    let endpoint = "../api/";
    let data = {"job_id": currentJobId};
    if(index == 1)
    {
        endpoint += "apply.php";
        
        for(let i=1; i<=3; i++)
            if($(`#question${i}`).length > 0)
                data[`question${i}_answer`] = $(`#question${i}`).val();
    }
    else if(index == 2)
        endpoint += "save_job.php";
    else
        endpoint += "hide_job.php";

    $.ajax({
        url: endpoint,
        method: "POST",
        data: JSON.stringify(data),
        contentType: "application/x-www-form-urlencoded",
        dataType: "json",
        beforeSend: function(xmlhttp) {
            xmlhttp.setRequestHeader("Authorization", "Bearer " + bearerToken);
        },   
        success: function(response) {
            if(index == 1)
                $(".modal-wrapper").html("<p>Your application was sent.</p>");
            else
                $(".modal-wrapper").html("<p>" + response["message"] + "</p>");
        },
        error: function(xmlhttp) {
            $(".modal-wrapper").html("<p>" + JSON.parse(xmlhttp.responseText)["message"] + "</p>");
        },
        complete: function() {
            $(".modal-wrapper").append("<button class='search-button' onclick='toggleModal(null)'>Close</button>");
        }
    });
}

function checkAnswers()
{
    let isEmpty = false;

    for(let i=0; i<questions.length; i++)
        if($(`#question${i + 1}`).val() == "")
        {
            $(".warning-text").eq(i).css("display", "inline");
            isEmpty = true;
        }
        else
            $(".warning-text").eq(i).css("display", "none");

    if(!isEmpty)
        postRequest(1);
}

//helper function
function redirect()
{
    window.location.href = "../applicants?id=" + currentJobId;
}