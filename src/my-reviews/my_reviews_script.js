$(document).ready(function() {
    $.ajax({
        url: "../api/get_my_reviews.php",
        method: "GET",
        data: {"page": currentPage, "limit": limit},
        contentType: "application/x-www-form-urlencoded",
        dataType: "json",
        beforeSend: function(xmlhttp) {
            xmlhttp.setRequestHeader("Authorization", "Bearer " + getCookie("jwt"));
        },
        success: function(response) {
            if(response["total_count"] == 0)
            {
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
            url: "../api/get_my_reviews.php",
            method: "GET",
            data: {"page": currentPage, "limit": limit},
            contentType: "application/x-www-form-urlencoded",
            dataType: "json",
            beforeSend: function(xmlhttp) {
                xmlhttp.setRequestHeader("Authorization", "Bearer " + getCookie("jwt"));
            },
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

let currentPage = 1;
let limit = 4;
let loadedAllData = false;

//render data on page
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

    reviewContent.append("<p> @ <a href='../views/companies.php?id=" + data["company_id"] + "'>" + data["company_name"] + "</a></p>");
    reviewContent.append("<p>" + data["job_type"] + ", " + data["employment_status"] + " employee</p>");
    reviewContent.append(rating);
    reviewContent.append(pros);
    reviewContent.append(cons);

    card.append(topRow);
    card.append(reviewContent);

    const buttons = $("<div class='buttons'></div>");
    buttons.append(`<button class='search-button' onclick='editReview(${data["id"]}, ${data["company_id"]})'>Edit</button>`);
    buttons.append(`<button class='search-button' onclick='toggleModal(${data["id"]})'>Delete</button>`);
    card.append(buttons);

    card.append(buttons);

    return card;
}

//edit + delete review
function editReview(reviewId, companyId)
{
    const hashedId = btoa(btoa(reviewId) + "." + Math.round(Date.now() / 1000) + "." + getCookie("jwt").split(".")[2]);

    window.location.href = `../post-review?id=${companyId}&edit=${hashedId}`;
}

function deleteReview(id)
{
    $.ajax({
        url: "../api/remove_review.php",
        method: "DELETE",
        data: JSON.stringify({"review_id": id}),
        contentType: "application/x-www-form-urlencoded",
        beforeSend: function(xmlhttp) {
            xmlhttp.setRequestHeader("Authorization", "Bearer " + getCookie("jwt"));
        },        
        success: function() {
            toggleModal(null);
            location.reload();
        }
    });
}

//helper functions
function toggleModal(id)
{
    if($(".modal").eq(0).css("display") == "none")
    {
        $(".modal-wrapper").empty();
        $(".modal-wrapper").append("<p>Are you sure you want to delete this review?</p>");
        $(".modal-wrapper").append(`<button class='search-button' onclick='deleteReview(${id})'>Confirm</button>`);

        $(".modal").eq(0).css("display", "block");
        $(".modal-wrapper").css("display", "block");
    }
    else
    {
        $(".modal").eq(0).css("display", "none");
        $(".modal-wrapper").css("display", "none");
    }
}