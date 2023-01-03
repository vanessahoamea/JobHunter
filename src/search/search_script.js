$(document).ready(function() {
    //load job data
    const limit = 5;
    const interval = 5;
    $.ajax({
        url: "../api/get_job_postings.php",
        method: "GET",
        data: {"company_id": 0, "page": 1, "limit": limit},
        contentType: "application/x-www-form-urlencoded",
        dataType: "json",
        success: function(response) {
            totalJobs = response["total_count"];
            pageCount = Math.ceil(response["total_count"] / limit);
            for(let page=1; page<=pageCount; page++)
                $("#pagination").append(`<div class='page' onclick='displayJobs(${page}, ${limit}, ${interval}, ${pageCount}, null)'>${page}</div>`);
            
            //quick jump to first and last pages
            $("#pagination").prepend(`<div id='quick-first' class='page' onclick='displayJobs(1, ${limit}, ${interval}, ${pageCount}, null)' style='display: none;'>first</div>`);
            $("#pagination").append(`<div id='quick-last' class='page' onclick='displayJobs(${pageCount}, ${limit}, ${interval}, ${pageCount}, null)' style='display: none;'>last</div>`);
            
            $(".card").remove();
            if(response["jobs"].length == 0)
                $(".job-postings").append("<p><i>Nothing to see here.</i></p>");
            else
            {
                $(".job-postings").prepend("<h2 class='total-jobs'>Found " + totalJobs + " jobs.</h2>");
                displayJobs(1, limit, interval, pageCount, response["jobs"]);
            }
        }
    });

    //create skill tags
    $("#skills").on("keyup", function(e) {
        if(e.key == "," || e.key == "Enter")
        {
            let text = e.target.value.replace(/\s+/g, " ");
            if(text.length > 0 && !skillsArray.includes(text))
                text.split(",").forEach(skill => {
                    skill = skill.trim();
                    if(skill.length == 0 || skillsArray.includes(skill))
                        return;

                    skillsArray.push(skill);
                    const listItem = $("<li><i class='fa-solid fa-xmark fa-fw' onclick='removeSkill(this)'></i>" + skill + "</li>");
                    $(listItem).insertBefore("#skills");
                });
            
            e.target.value = "";
        }
    });

    //autocomplete location search
    const apiKey = "ef42d07733984937ada80ca08c261076";
    let selectedLocation = null;
    $("#location").on("input", function() {
        let text = $(this).val();
        let container = $("#listing-container");

        if(selectedLocation != null && selectedLocation != text)
        {
            $("#location-lat").val("");
            $("#location-lon").val("");
        }

        if(text == "")
        {
            container.empty();
            container.css("display", "none");
        }
        else
        {
            fetch(`https://api.geoapify.com/v1/geocode/autocomplete?text=${text}&format=json&apiKey=${apiKey}`)
            .then(response => response.json())
            .then(response => {
                response = response["results"];
                if(response.length > 0)
                {
                    container.empty();
                    container.css("display", "flex");
                    for(let i=0; i<response.length; i++)
                    {
                        container.append("<li class='listing'><i class='fa-solid fa-location-dot fa-fw'></i>" + response[i]["formatted"] + "</li>");
                        container.append("<p style='display: none;'>" + response[i]["lat"] + "</p>");
                        container.append("<p style='display: none;'>" + response[i]["lon"] + "</p>");
                    }
                }
            })
            .catch();
        }
    });

    $(document).on("click", ".listing", function() {
        $("#location").val($(this).text());
        $("#location-lat").val($(this).next().text());
        $("#location-lon").val($(this).next().next().text());
        $("#listing-container").css("display", "none");
        selectedLocation = $(this).text();
    });

    $(document).on("click", "#main", function() {
        $("#listing-container").css("display", "none");
    });
});

let totalJobs = 0;
let pageCount = 0;

//handle skills
let skillsArray = [];
function removeSkill(skill)
{
    const index = skillsArray.indexOf($(skill).parent().text());
    if(index > -1)
    {
        skillsArray.splice(index, 1);
        $(skill).parent().remove();
    }
}

//render data on page
function buildJobCard(data)
{
    let card = $("<div class='card'></div>");
    let topRow = $("<div class='top-row'></div>");
    let cardInformation = $("<div class='card-information'></div>");
    let jobRequirements = $("<div class='job-requirements'></div>");

    new Promise((resolve, reject) => {
        $.ajax({
            url: "../api/get_company.php",
            method: "GET",
            data: {"id": data["company_id"]},
            contentType: "application/x-www-form-urlencoded",
            dataType: "json",
            success: function(response) {
                resolve(response);
            },
            error: function() {
                reject();
            }
        });
    }).then((response) => {
        topRow.append("<h2><a href='../views/jobs.php?id=" + data["id"] + "'>" + data["title"] + "</a></h2>");
        topRow.append("<p class='date-posted'>" + data["date_posted"] + "</p>");

        if(data["skills"] != null)
        {
            data["skills"] = JSON.parse(data["skills"]);
            for(let i=0; i<data["skills"].length; i++)
                jobRequirements.append("<div class='skill-tag'>" + data["skills"][i] + "</div>");
        }

        cardInformation.append("<i class='fa-solid fa-building-user fa-fw'></i><a href='../views/companies.php?id=" + data["company_id"] + "' style='font-weight: bold;'>" + response["company_name"] + "</a>");
        cardInformation.append("<p><i class='fa-solid fa-suitcase fa-fw'></i>" + data["type"] + " (" + data["physical"] + ") &#x2022; " + data["level"] + "</p>");
        if(data["salary"] != null)
            cardInformation.append("<p><i class='fa-solid fa-money-bill-wave fa-fw'></i>" + data["salary"] + " (per month)</p>");
        cardInformation.append("<p><i class='fa-solid fa-location-dot fa-fw'></i>in " + data["location_name"] + "</p>");
        cardInformation.append(jobRequirements);

        card.append("<div class='job-id' style='display: none;'>" + data["id"] + "</div>");
        card.append(topRow);
        card.append(cardInformation);

        const buttons = $("<div class='buttons'></div>");
        buttons.append("<button class='search-button' onclick='redirect(this)'>view details</button>");
        if($("#can-apply").length != 0)
            buttons.append("<button class='search-button' onclick='redirect(null)'>apply</button>");
        card.append(buttons);
    }).catch(() => {});

    return card;
}

function displayJobs(currentPage, limit, interval, pageCount, jobsArray)
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
                    data: {"company_id": 0, "page": currentPage, "limit": limit},
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
            }).catch(() => {});
        }
    }
    //otherwise, we can just display the fully-loaded page and hide the others
    else
        $("#page" + currentPage).css("display", "block");
}

//helper function
function redirect(target)
{
    if(target != null)
    {
        const id = $(target).parent().parent().children().eq(0).text();
        window.location.href = `../views/jobs.php?id=${id}`;
    }
    else
    {
        //apply popup
    }
}