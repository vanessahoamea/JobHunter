$(document).ready(function() {
    const params = new Proxy(new URLSearchParams(window.location.search), {
        get: (searchParams, prop) => searchParams.get(prop),
    });
    companyId = params.id;

    //loading first batch of reviews
    $.ajax({
        url: "../api/get_reviews.php",
        method: "GET",
        data: {"company_id": companyId, "page": currentPage, "limit": limit},
        contentType: "application/x-www-form-urlencoded",
        dataType: "json",
        success: function(response) {
            if(response["total_count"] == 0)
            {
                $("#reviews-stats").remove();
                $(".reviews").html("<p><i>Nothing to see here.</i><p>");
                return;
            }

            response = response["reviews"];

            $(".reviews").empty();
            for(let i=0; i<response.length; i++)
            {
                let card = buildReviewCard(response[i]);
                $(".reviews").append(card);
            }
        }
    });

    //setting the company's rating
    $.ajax({
        url: "../api/get_review_stats.php",
        method: "GET",
        data: {"company_id": companyId},
        contentType: "application/x-www-form-urlencoded",
        dataType: "json",
        success: function(response) {
            let stars = $("#company-rating").children(".fa-star");
            for(let i=0; i<response["rating"]; i++)
                stars.eq(i).addClass("full-star");
            
            let reviewStats = $("#reviews-stats");
            reviewStats.removeClass().removeAttr("style");;
            reviewStats.css("font-weight", "bold");
            reviewStats.text(`Calculated from ${response["total_count"]} user-submitted reviews`);

            for(let i=1; i<=5; i++)
                distribution[i] = response[i];
        }
    });

    //showing graph in modal
    $("#company-rating").children(".fa-star").on("click", () => toggleModal());

    //get company name
    $.ajax({
        url: "../views/companies.php?id=" + companyId,
        async: true,
        success: function(data) {
            const matches = data.match(/<title>(.*?)<\/title>/);
            const title = matches[0].split(">")[1].split("<")[0];
            $("#main").prepend(`<h1>Reviews for <a href="../views/companies.php?id=${companyId}">${title}</a></h1>`);
        }
    });
});

$(window).click(function(event) {
    if(event.target == $(".modal")[0])
        toggleModal();
});

window.addEventListener("scroll", function() {
    const { scrollTop, scrollHeight, clientHeight } = document.documentElement;
    const defaultHtml = `
        <div class="card default-html">
            <div class="top-row">
                <h2 class="skeleton skeleton-text-single"></h2>
            </div>
            <div class="review-content">
                <div class="skeleton skeleton-text"></div>
                <div class="skeleton skeleton-text"></div>
                <div class="skeleton skeleton-text"></div>
            </div>
        </div>
    `;

    if(clientHeight + scrollTop >= scrollHeight - 5 && !loadedAllData)
    {
        $(".reviews").append(defaultHtml);
        $(".reviews").append(defaultHtml);

        currentPage += 1;
        $.ajax({
            url: "../api/get_reviews.php",
            method: "GET",
            data: {"company_id": companyId, "page": currentPage, "limit": limit},
            contentType: "application/x-www-form-urlencoded",
            dataType: "json",
            success: function(response) {
                if(response["next"] == null)
                    loadedAllData = true;

                response = response["reviews"];
    
                $(".default-html").remove();
                for(let i=0; i<response.length; i++)
                {
                    let card = buildReviewCard(response[i]);
                    $(".reviews").append(card);
                }
            }
        });
    }
});

let companyId = -1;
let currentPage = 1;
let limit = 4;
let loadedAllData = false;

let chart = null;
let distribution = {1: 0, 2: 0, 3: 0, 4: 0, 5: 0};

function buildReviewCard(data)
{
    let card = $("<div class='card'></div>");
    let topRow = $("<div class='top-row'></div>");
    let reviewContent = $("<div class='review-content'></div>");

    topRow.append("<h2>" + data["job_title"] + "</h2>");
    topRow.append("<p class='date-posted'>" + data["date_posted"] + "</p>");

    let rating = $("<div class='card-stars-container'></div>");
    for(let i=0; i<5; i++)
    {
        if(i < data["rating"])
            rating.append("<i class='fa-solid fa-star full-star'></i>");
        else
            rating.append("<i class='fa-solid fa-star'></i>");
    }

    let pros = $("<div class='review-text'></div>");
    pros.append("<h3>Pros:</h3>");
    pros.append("<p>" + data["pros"] + "</p>");

    let cons = $("<div class='review-text'></div>");
    cons.append("<h3>Cons:</h3>");
    cons.append("<p>" + data["cons"] + "</p>");

    reviewContent.append("<p>" + data["job_type"] + ", " + data["employment_status"] + " employee</p>");
    reviewContent.append(rating);
    reviewContent.append(pros);
    reviewContent.append(cons);

    card.append(topRow);
    card.append(reviewContent);

    return card;
}

//helper functions
function toggleModal()
{
    $(".modal-content").css("width", "70vw");
    if($(".modal").eq(0).css("display") == "none")
    {
        $(".modal-wrapper").empty();
        $(".modal-wrapper").append("<h2>Overall distribution</h2>");
        $(".modal-wrapper").append("<canvas id='canvas' style='width: 100%; max-width: 60vw; margin: 0 auto;'></canvas>");

        chart = new Chart("canvas", {
            type: "horizontalBar",
            data: {
                labels: ["1 star", "2 stars", "3 stars", "4 stars", "5 stars"],
                datasets: [{
                    backgroundColor: ["#BAF2BB", "#BAF2D8", "#BAD7F2", "#F2BAC9", "#F2E2BA"],
                    data: Object.values(distribution),
                }]
            },
            options: {
                legend: {
                    display: false
                },
                scales: {
                    xAxes: [{
                        gridLines: {
                            display:false
                        },
                        ticks: {
                            precision: 0,
                            beginAtZero: true
                        }
                    }],
                    yAxes: [{
                        gridLines: {
                            display:false
                        }
                    }]
                }
            }
        });
        
        $(".modal").eq(0).css("display", "block");
        $(".modal-wrapper").css("display", "block");
    }
    else
    {
        $(".modal").eq(0).css("display", "none");
        $(".modal-wrapper").css("display", "none");

        if(chart != null)
        {
            chart.destroy();
            chart = null;
        }
    }
}