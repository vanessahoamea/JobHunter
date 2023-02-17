$(document).ready(function() {
    showJobs(0);
});

$(window).click(function(event) {
    if(event.target == $(".modal")[0])
        toggleModal(null);
});

let currentIndex = -1;
let jobCards = {};

function showJobs(index)
{
    if(currentIndex == index)
        return;
    else
        currentIndex = index;

    $(".top-buttons").children().eq(index).addClass("selected-button");
    $(".top-buttons").children().eq((index + 1) % 3).removeClass("selected-button");
    $(".top-buttons").children().eq((index + 2) % 3).removeClass("selected-button");

    const type = (index == 0) ? "bookmarked" : (index == 1) ? "hidden" : "applied";
    displayJobs(type);
}

function displayJobs(type)
{
    const defaultHtml = `
        <div class="card">
            <div class="top-row">
                <h2 class="skeleton skeleton-text-single"></h2>
            </div>
            <div class="card-information">
                <div class="skeleton skeleton-text"></div>
                <div class="skeleton skeleton-text"></div>
                <div class="skeleton skeleton-text"></div>
            </div>
        </div>
    `;

    //loading jobs for the first time
    if(!jobCards.hasOwnProperty(type))
    {
        $(".jobs").html(defaultHtml);
        $(".jobs").append(defaultHtml);
        jobCards[type] = [];

        $.ajax({
            url: "../api/get_my_jobs.php",
            method: "GET",
            data: {"type": type},
            contentType: "application/x-www-form-urlencoded",
            dataType: "json",
            beforeSend: function(xmlhttp) {
                if(getCookie("jwt") != "")
                    xmlhttp.setRequestHeader("Authorization", "Bearer " + getCookie("jwt"));
            },
            success: function(response) {
                response = response["data"];
                if(response.length == 0)
                {
                    $(".jobs").html("<p style='text-align: center'><i>Nothing to see here.</i></p>");
                    jobCards[type].push("<p style='text-align: center'><i>Nothing to see here.</i></p>");
                    return;
                }

                $(".jobs").empty();
                for(let i=0; i<response.length; i++)
                {
                    $.ajax({
                        url: "../api/get_job.php",
                        method: "GET",
                        data: {"id": response[i]["job_id"]},
                        contentType: "application/x-www-form-urlencoded",
                        dataType: "json",
                        success: function(data) {
                            card = buildJobCard(data);
                            $(".jobs").append(card);
                            jobCards[type].push(card);
                        }
                    });
                }
            }
        });
    }
    //once all the jobs have been loaded, we don't need to make anymore API calls
    else
    {
        $(".jobs").empty();
        jobCards[type].map((item) => { $(".jobs").append(item); });
    }
}

function buildJobCard(data)
{
    let card = $("<div class='card'></div>");
    let topRow = $("<div class='top-row'></div>");
    let cardInformation = $("<div class='card-information'></div>");
    let jobRequirements = $("<div class='job-requirements'></div>");

    topRow.append("<h2><a href='../views/jobs.php?id=" + data["id"] + "'>" + data["title"] + "</a></h2>");
    topRow.append("<p class='date-posted'>" + data["date_posted"] + "</p>");

    if(data["skills"] != null)
    {
        data["skills"] = JSON.parse(data["skills"]);
        for(let i=0; i<data["skills"].length; i++)
            jobRequirements.append("<div class='skill-tag'>" + data["skills"][i] + "</div>");
    }

    cardInformation.append("<i class='fa-solid fa-building-user fa-fw'></i><a href='../views/companies.php?id=" + data["company_id"] + "' style='font-weight: bold;'>" + data["company_name"] + "</a>");
    cardInformation.append("<p><i class='fa-solid fa-suitcase fa-fw'></i>" + data["type"] + " (" + data["physical"] + ") &#x2022; " + data["level"] + "</p>");
    if(data["salary"] != null)
        cardInformation.append("<p><i class='fa-solid fa-money-bill-wave fa-fw'></i>" + data["salary"] + " (per month)</p>");
    cardInformation.append("<p><i class='fa-solid fa-location-dot fa-fw'></i>in " + data["location_name"] + "</p>");
    cardInformation.append(jobRequirements);

    card.append(topRow);
    card.append(cardInformation);

    const buttons = $("<div class='buttons'></div>");
    buttons.append(`<button class='search-button' onclick='toggleModal(${data["id"]}, 2)'>Remove job</button>`);
    card.append(buttons);

    return card;
}

function toggleModal(id)
{
    let text = (currentIndex == 0) ? "<p>Are you sure you want to remove this job from your bookmarks?</p>"
               : (currentIndex == 1) ? "<p>Are you sure you want to make this job visible?</p>"
               : "<p>Are you sure you want to withdraw your application for this job?</p>";

    if($(".modal").eq(0).css("display") == "none")
    {
        $(".modal-wrapper").empty();
        $(".modal-wrapper").append(text);
        $(".modal-wrapper").append(`<button class='search-button' onclick='removeJob(${id})'>Confirm</button>`);

        $(".modal").eq(0).css("display", "block");
        $(".modal-wrapper").css("display", "block");
    }
    else
    {
        $(".modal").eq(0).css("display", "none");
        $(".modal-wrapper").css("display", "none");
    }
}

function removeJob(id)
{
    const type = (currentIndex == 0) ? "bookmarked" : (currentIndex == 1) ? "hidden" : "applied";
    const bearerToken = getCookie("jwt");
    $.ajax({
        url: "../api/remove_my_job.php",
        method: "DELETE",
        data: JSON.stringify({"job_id": id, "type": type}),
        contentType: "application/x-www-form-urlencoded",
        beforeSend: function(xmlhttp) {
            xmlhttp.setRequestHeader("Authorization", "Bearer " + bearerToken);
        },        
        success: function() {
            toggleModal(null);
            location.reload();
        }
    });
}