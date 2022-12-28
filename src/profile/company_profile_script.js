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
                $(".information-list").append("<li><i class='fa-solid fa-building fa-fw'></i>" + response["address"] + "</li>");
            if(response["website"] != null)
                $(".information-list").append("<li><i class='fa-solid fa-globe fa-fw'></i><a href='" + response["website"] + "'>" + response["website"] + "</a></li>");
        }
    });

    //job postings
    const limit = 5;
    const interval = 5;
    $.ajax({
        url: "../api/get_job_postings.php",
        method: "GET",
        data: {"company_id": id, "page": 1, "limit": limit},
        contentType: "application/x-www-form-urlencoded",
        dataType: "json",
        success: function(response) {
            const pageCount = Math.ceil(response["total_count"] / limit);
            for(let page=1; page<=pageCount; page++)
                $("#pagination").append(`<div class='page' onclick='displayJobs(${page}, ${limit}, ${interval}, ${pageCount}, ${id}, null)'>${page}</div>`);
            
            //quick jump to first and last pages
            $("#pagination").prepend(`<div id='quick-first' class='page' onclick='displayJobs(1, ${limit}, ${interval}, ${pageCount}, ${id}, null)' style='display: none;'>first</div>`);
            $("#pagination").append(`<div id='quick-last' class='page' onclick='displayJobs(${pageCount}, ${limit}, ${interval}, ${pageCount}, ${id}, null)' style='display: none;'>last</div>`);
            
            $(".card").remove();
            if(response["jobs"].length == 0)
                $("<p><i>This company has no job postings.</i></p>").insertBefore("#pagination");
            else
                displayJobs(1, limit, interval, pageCount, id, response["jobs"]);
        }
    });
});

function buildJobCard(data)
{
    let card = $("<div class='card'></div>");
    let topRow = $("<div class='top-row'></div>");
    let cardInformation = $("<div class='card-information'></div>");
    let jobRequirements = $("<div class='job-requirements'></div>");

    topRow.append("<h2>" + data["title"] + "</h2>");
    topRow.append("<p class='date-posted'>" + data["date_posted"] + "</p>");

    data["skills"] = JSON.parse(data["skills"]);
    for(let i=0; i<data["skills"].length; i++)
        jobRequirements.append("<div class='skill-tag'>" + data["skills"][i] + "</div>");

    cardInformation.append("<p><i class='fa-solid fa-suitcase fa-fw'></i>" + data["type"] + " (" + data["physical"] + ") &#x2022; " + data["level"] + "</p>");
    if(data["salary"] != null)
        cardInformation.append("<p><i class='fa-solid fa-money-bill-wave fa-fw'></i>" + data["salary"] + " (per month)</p>");
    cardInformation.append("<p><i class='fa-solid fa-location-dot fa-fw'></i>in " + data["location_name"] + "</p>");
    cardInformation.append(jobRequirements);

    card.append("<div class='job-id' style='display: none;'></div>"); //for editing/deleting
    card.append(topRow);
    card.append(cardInformation);

    //edit/delete buttons for each job
    if($("#self-view").length != 0)
    {
        const buttons = $("<div class='buttons'></div>");
        buttons.append("<button class='section-button'><i class='fa-solid fa-pen-to-square'></i>edit</button>");
        buttons.append("<button class='section-button'><i class='fa-solid fa-trash'></i>delete</button>");
        card.append(buttons);
    }

    return card;
}

function displayJobs(currentPage, limit, interval, pageCount, id, jobsArray)
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
        jobsPage.insertBefore("#pagination");

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
                    data: {"company_id": id, "page": currentPage, "limit": limit},
                    contentType: "application/x-www-form-urlencoded",
                    dataType: "json",
                    success: function(response) {
                        jobsPage.empty();
                        for(let i=0; i<response.length; i++)
                        {
                            card = buildJobCard(response[i]);
                            jobsPage.append(card);
                        }
                        
                        resolve();
                    },
                    error: function() {
                        reject();
                    }
                });
            }).then(() => {}).catch(() => {});
        }
    }
    //otherwise, we can just display the fully-loaded page and hide the others
    else
        $("#page" + currentPage).css("display", "block");
}

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