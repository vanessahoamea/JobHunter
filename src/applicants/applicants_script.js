$(document).ready(function() {
    //load candidates
    const params = new Proxy(new URLSearchParams(window.location.search), {
        get: (searchParams, prop) => searchParams.get(prop),
    });
    const id = params.id;
    const years = params.minimum_years != "" ? params.minimum_years : "0";
    const sort = params.sort != "" ? (params.sort == "true") : false;
    const jobHoppers = params.hide_job_hoppers != "" ? (params.hide_job_hoppers == "true") : false;
    const descriptions = params.hide_no_descriptions != "" ? (params.hide_no_descriptions == "true") : false;

    $("#minimum-years").val(years);
    $("input[name='sort']").prop("checked", sort);
    $("input[name='job-hoppers']").prop("checked", jobHoppers);
    $("input[name='descriptions']").prop("checked", descriptions);

    const bearerToken = getCookie("jwt");
    $.ajax({
        url: "../api/get_applicants.php",
        method: "GET",
        data: {"job_id": id},
        contentType: "application/x-www-form-urlencoded",
        dataType: "json",
        beforeSend: function(xmlhttp) {
            xmlhttp.setRequestHeader("Authorization", "Bearer " + bearerToken);
        },
        success: function(response) {
            $(".candidates").empty();

            if(response["data"].length == 0)
            {
                $(".candidates").append("<i>Nobody has applied to this job yet.</i>");
                return;
            }
            
            let allCandidateCards = [];
            new Promise((resolve) => {
                response["data"].forEach((candidate, index) => {
                    const candidatePicture = $("<div class='candidate-picture'></div>");
                    candidatePicture.append("<img class='candidate-picture skeleton' src='../assets/default.jpg' alt='Candidate picture'>");
                    
                    const candidateData = $("<div class='candidate-data'></div>");
                    candidateData.append(candidatePicture);
                    candidateData.append(`<h2><a href='../views/candidates.php?id=${candidate["id"]}'>${candidate["first_name"]} ${candidate["last_name"]}</a></h2>`);
    
                    const buttons = $("<div class='buttons'></div>");
                    buttons.append(`<button class='search-button' onclick='redirect(${candidate["id"]}, 0)'><i class='fa-regular fa-address-card fa-fw'></i>CV</button>`);
                    buttons.append(`<button class='search-button' onclick='toggleModal(${candidate["id"]})'><i class='fa-solid fa-magnifying-glass fa-fw'></i>More data</button>`);
    
                    const candidateCard = $("<div class='candidate-card'></div>");
                    candidateCard.append(candidateData);
                    candidateCard.append(buttons);
    
                    $.ajax({
                        url: "../api/get_experience_data.php",
                        method: "GET",
                        data: {"id": candidate["id"]},
                        contentType: "application/x-www-form-urlencoded",
                        dataType: "json",
                        success: function(experience) {
                            if(experience["years"] < years)
                                return;
                            if(jobHoppers && experience["average_employment_period"] < 6)
                                return;
                            if(descriptions && !experience["adds_descriptions"])
                                return;
                            
                            allCandidateCards.push({card: candidateCard, total_months: experience["total_months"]});
                        },
                        complete: function() {
                            if(index == response["data"].length - 1)
                            {
                                if(sort)
                                    allCandidateCards.sort((a, b) => b.total_months - a.total_months);
                                resolve(allCandidateCards);
                            }
                        }
                    });
                });
            }).then(
                (result) => {
                    if(result.length == 0)
                        $(".candidates").append("<i>Nobody has applied to this job yet.</i>");
                    result.forEach((object) => $(".candidates").append(object.card));
                }
            );
        }
    });
});

function getExperienceData(id)
{
    return new Promise((resolve, reject) => {
        $.ajax({
            url: "../api/get_experience_data.php",
            method: "GET",
            data: {"id": id},
            contentType: "application/x-www-form-urlencoded",
            dataType: "json",
            success: function(response) {
                let string = "<p><i class='fa-regular fa-calendar-days fa-fw'></i>Has a total of <b>" + 
                response["years"] + " years and " + 
                response["months"] + " months</b> of work experience</p>";

                string += "<p><i class='fa-solid fa-business-time fa-fw'></i><b>";
                if(response["average_employment_period"] >= 6)
                    string += "Is likely to stay</b> at one workplace for a longer period of time</p>";
                else
                    string += "Not likely to stay</b> at one workplace for long periods of time, might be a job hopper</p>";

                string += "<p><i class='fa-solid fa-pencil fa-fw'></i><b>";
                if(response["adds_descriptions"])
                    string += "Provides descriptions</b> for most of their previous roles</p>";
                else
                    string += "Doesn't provide descriptions</b> for most of their previous roles</p>";

                resolve(string);
            },
            error: function() {
                reject();
            }
        });
    });
}

//helper functions
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

function redirect(id, action)
{
    if(action == 0)
        window.location.href = `../views/candidates.php?id=${id}`;
    else
        window.location.href = `../views/jobs.php?id=${id}`;
}

function applyFilters(id)
{
    const minimumYears = $("#minimum-years").val();
    const sort = $("#sort").prop("checked");
    const jobHoppers = $("#job-hoppers").prop("checked");
    const descriptions = $("#descriptions").prop("checked");
    
    let params = {
        minimum_years: minimumYears,
        sort: sort,
        hide_job_hoppers: jobHoppers,
        hide_no_descriptions: descriptions
    };
    params = new URLSearchParams(
        Object.fromEntries(Object.entries(params))
    );

    window.location.href = "index.php?id=" + id + "&" + params.toString();
}

async function toggleModal(id)
{
    if($(".modal").eq(0).css("display") == "none")
    {
        const data = await getExperienceData(id);

        $(".modal-wrapper").empty();
        $(".modal-wrapper").append($(data));
        $(".modal-wrapper").append("<button class='search-button' onclick='toggleModal(null)'>Close</button>");

        $(".modal").eq(0).css("display", "block");
        $(".modal-wrapper").css("display", "block");
    }
    else
    {
        $(".modal").eq(0).css("display", "none");
        $(".modal-wrapper").css("display", "none");
    }
}

$(window).click(function(event) {
    if(event.target == $(".modal")[0])
        toggleModal(null);
});