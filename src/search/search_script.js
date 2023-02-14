$(document).ready(function() {
    //load job data
    const params = new Proxy(new URLSearchParams(window.location.search), {
        get: (searchParams, prop) => searchParams.get(prop),
    });
    data = {
        keywords: params.keywords != null ? decodeURIComponent(params.keywords) : null,
        location_name: params.location_name != null ? decodeURIComponent(params.location_name) : null,
        location_lat: params.location_lat != null ? decodeURIComponent(params.location_lat) : null,
        location_lon: params.location_lon != null ? decodeURIComponent(params.location_lon) : null,
        skills: params.skills != null ? params.skills.split(",").map(skill => decodeURIComponent(skill)).join(",") : null,
        type: params.type != null ? params.type.split(",").map(type => decodeURIComponent(type)).join(",") : null,
        level: params.level != null ? params.level.split(",").map(level => decodeURIComponent(level)).join(",") : null,
        salary: params.salary
    }
    data = Object.fromEntries(Object.entries(data).filter(([_, value]) => value != null));
    setFilters();

    $.ajax({
        url: "../api/get_job_postings.php",
        method: "GET",
        data: {...data, "company_id": 0, "page": 1, "limit": limit},
        contentType: "application/x-www-form-urlencoded",
        dataType: "json",
        beforeSend: function(xmlhttp) {
            if(getCookie("jwt") != "")
                xmlhttp.setRequestHeader("Authorization", "Bearer " + getCookie("jwt"));
        },
        success: function(response) {
            const pageCount = Math.ceil(response["total_count"] / limit);
            for(let page=1; page<=pageCount; page++)
                $("#pagination").append(`<div class='page' onclick='displayJobs(${page}, ${pageCount}, null)'>${page}</div>`);
            
            //quick jump to first and last pages
            $("#pagination").prepend(`<div id='quick-first' class='page' onclick='displayJobs(1, ${pageCount}, null)' style='display: none;'>first</div>`);
            $("#pagination").append(`<div id='quick-last' class='page' onclick='displayJobs(${pageCount}, ${pageCount}, null)' style='display: none;'>last</div>`);
            
            $(".card").remove();
            if(response["jobs"].length == 0)
                $(".job-postings").append("<p style='text-align: center'><i>Nothing to see here.</i></p>");
            else
                displayJobs(1, pageCount, response["jobs"]);
        }
    });

    //filter jobs
    $("#search").on("keyup", function(event) {
        if(event.key == "Enter")
            applyFilters();
    });

    $("#search-button").on("click", function() {
        applyFilters();
    });
});

let currentJobId = -1;
const limit = 4;
const interval = 5;
let data = null;

//filter jobs
function applyFilters()
{
    const keywords = encodeURIComponent($("#search").val());
    const locationName = encodeURIComponent($("#location").val());
    const locationLat = encodeURIComponent($("#location-lat").val());
    const locationLon = encodeURIComponent($("#location-lon").val());
    const skills = skillsArray.map(skill => encodeURIComponent(skill)).join(",");
    const type = [];
    $("input[name='job-type']:checked").each(function() {
        type.push(encodeURIComponent($(this).val()));
    });
    const level = [];
    $("input[name='level']:checked").each(function() {
        level.push(encodeURIComponent($(this).val()));
    });
    const salary = $("#salary").prop("checked");
    
    let params = {
        keywords: keywords,
        location_name: locationName,
        location_lat: locationName != "" ? locationLat : "",
        location_lon: locationName != "" ? locationLon : "",
        skills: skills,
        type: type.join(","),
        level: level.join(","),
        salary: salary
    };
    params = new URLSearchParams(
        Object.fromEntries(Object.entries(params).filter(([_, value]) => value != ""))
    );

    window.location.href = "index.php?" + params.toString();
}

function setFilters()
{
    $("#search").val(data.keywords);
    $("#location").val(data.location_name);
    $("#location-lat").val(data.location_lat);
    $("#location-lon").val(data.location_lon);
    if(data.skills != null)
    {
        skillsArray = data.skills.split(",");
        skillsArray.forEach(skill => {
                const listItem = $("<li><i class='fa-solid fa-xmark fa-fw' onclick='removeSkill(this)'></i>" + skill + "</li>");
                $(listItem).insertBefore("#skills");
            }
        );
    }
    if(data.type != null)
        data.type.split(",").forEach(type => $(`input[name='job-type'][value='${type}']`).prop("checked", true));
    if(data.level != null)
        data.level.split(",").forEach(level => $(`input[name='job-type'][value='${level}']`).prop("checked", true));
    if(data.salary == "true")
        $("input[name='salary'][value='on']").prop("checked", true);
}

//render data on page
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

    card.append("<div class='job-id' style='display: none;'>" + data["id"] + "</div>");
    card.append(topRow);
    card.append(cardInformation);

    const buttons = $("<div class='buttons'></div>");
    buttons.append(`<button class='search-button' onclick='getId(${data["id"]}, 0)'>View details</button>`);
    if($("#can-apply").length != 0)
    {
        buttons.append(`<button class='search-button' onclick='getId(${data["id"]}, 1)'>Apply</button>`);
        buttons.append(`<button class='search-button' onclick='getId(${data["id"]}, 2)'>Save</button>`);
        buttons.append(`<button class='search-button' onclick='getId(${data["id"]}, 3)'>Hide</button>`);
    }
    card.append(buttons);

    return card;
}

function displayJobs(currentPage, pageCount, jobsArray)
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

    //hide the contents of the other pages
    $(".loaded-page").css("display", "none");

    //only displaying a few page buttons at once, if we have too many and it becomes cluttered
    let maxLeft = currentPage - Math.floor(interval / 2);
    let maxRight = currentPage + Math.floor(interval / 2);
    if(maxLeft < 1)
    {
        maxLeft = 1;
        maxRight = interval;
    }
    if(maxRight > pageCount)
    {
        maxLeft = Math.max(1, (pageCount - interval + 1));
        maxRight = pageCount;
    }

    $(".page").css("display", "none");
    $(".page").removeClass("active");
    $(".page").eq(currentPage).addClass("active");
    for(let i=maxLeft; i<=maxRight; i++)
        $(".page").eq(i).css("display", "flex");
    
    if(maxLeft > 1)
        $("#quick-first").css("display", "flex");
    if(maxRight < pageCount)
        $("#quick-last").css("display", "flex");

    //if the items on the selected page haven't been loaded yet, we will load them
    if($("#page" + currentPage).length == 0)
    {
        let jobsPage = $("<div id='page" + currentPage + "' class='loaded-page'></div>");
        $(".job-postings").append(jobsPage);

        //when the page first loads, we will populate it with the data fetched above
        if(currentPage == 1)
        {
            for(let i=0; i<jobsArray.length; i++)
            {
                card = buildJobCard(jobsArray[i]);
                jobsPage.append(card);
            }
        }
        else
        {
            jobsPage.html(defaultHtml);
            jobsPage.append(defaultHtml);

            new Promise((resolve, reject) => {
                $.ajax({
                    url: "../api/get_job_postings.php",
                    method: "GET",
                    data: {...data, "company_id": 0, "page": currentPage, "limit": limit},
                    contentType: "application/x-www-form-urlencoded",
                    dataType: "json",
                    success: function(response) {
                        resolve(response["jobs"]);
                    },
                    error: function() {
                        reject();
                    }
                });
            }).then((response) => {
                jobsPage.empty();
                for(let i=0; i<response.length; i++)
                {
                    card = buildJobCard(response[i]);
                    jobsPage.append(card);
                }
            }).catch(() => { return; });
        }
    }
    //otherwise, we can just display the fully-loaded page and hide the others
    else
        $("#page" + currentPage).css("display", "block");
}

//view + apply for job
$(window).click(function(event) {
    if(event.target == $(".modal")[0])
    getId(null, 1);
});

function getId(id, action)
{
    currentJobId = id;

    if(action == 0)
        window.location.href = `../views/jobs.php?id=${currentJobId}`;
    else
    {
        if($(".modal").eq(0).css("display") == "none")
        {
            $(".modal-wrapper").empty();

            if(action == 1)
                $(".modal-wrapper").append("<p>Would you like to send an application for this job?</p>");
            else if(action == 2)
                $(".modal-wrapper").append("<p>Would you like to save this job for later?</p>");
            else
                $(".modal-wrapper").append("<p>Would you like to hide this job posting?</p>");

            $(".modal-wrapper").append("<button class='search-button' onclick='selectJob(" + action + ")'>Confirm</button>");

            $(".modal").eq(0).css("display", "block");
            $(".modal-wrapper").css("display", "block");
        }
        else
        {
            $(".modal").eq(0).css("display", "none");
            $(".modal-wrapper").css("display", "none");
        }
    }
}

function selectJob(action)
{
    const bearerToken = getCookie("jwt");
    if(bearerToken == "")
    {
        window.location.href = "../login";
        return;
    }

    let endpoint = "../api/";
    endpoint += (action == 1) ? "apply.php" : (action == 2) ? "save_job.php" : "hide_job.php";

    $.ajax({
        url: endpoint,
        method: "POST",
        data: JSON.stringify({"job_id": currentJobId}),
        contentType: "application/x-www-form-urlencoded",
        dataType: "json",
        beforeSend: function(xmlhttp) {
            xmlhttp.setRequestHeader("Authorization", "Bearer " + bearerToken);
        },   
        success: function() {
            if(action == 1)
                $(".modal-wrapper").html("<p>Your application was sent.</p>");
            else
                window.location.href = `../search`;
        },
        error: function(xmlhttp) {
            $(".modal-wrapper").html("<p>" + JSON.parse(xmlhttp.responseText)["message"] + "</p>");
        },
        complete: function() {
            if(action == 1)
                $(".modal-wrapper").append("<button class='search-button' onclick='getId(null, 1)'>Close</button>");
        }
    });
}