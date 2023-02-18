$(document).ready(function() {
    const params = new Proxy(new URLSearchParams(window.location.search), {
        get: (searchParams, prop) => searchParams.get(prop),
    });
    const id = params.id;

    $.ajax({
        url: "../api/get_reviews.php",
        method: "GET",
        data: {"company_id": id},
        contentType: "application/x-www-form-urlencoded",
        dataType: "json",
        success: function(response) {
            response = response["data"];
            let rating = 0;

            $(".reviews").empty();
            for(let i=0; i<response.length; i++)
            {
                rating += response[i]["rating"];

                //displaying user reviews
                let card = buildReviewCard(response[i]);
                $(".reviews").append(card);
            }

            rating = Math.round(rating / response.length);
            
            //setting the company's rating
            let stars = $("#company-rating").children(".fa-star");
            for(let j=0; j<rating; j++)
                stars.eq(j).addClass("full-star");
            
            let reviewStats = $("#reviews-stats");
            reviewStats.removeClass().removeAttr("style");;
            reviewStats.css("font-weight", "bold");
            reviewStats.text(`Calculated from ${response.length} user-submitted reviews`);
        }
    });
});

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

//helper function
function redirect(id)
{
    window.location.href = "../views/companies.php?id=" + id;
}