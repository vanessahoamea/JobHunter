$(document).ready(function() {
    const params = new Proxy(new URLSearchParams(window.location.search), {
        get: (searchParams, prop) => searchParams.get(prop),
    });
    companyId = params.id;

    //fill in review data if in edit mode
    if($("#edit-mode").length > 0)
    {
        reviewId = atob(atob(params.edit).split(".")[0]);

        fillData();
        $(".submit-button").text("Submit changes");
    }
});

let companyId = -1;
let reviewId = -1;

function checkEmtpyValues(title, pros, cons, rating)
{
    const values = [title, pros, cons, rating];
    const warnings = $(".warning-text");
    let returnValue = false;

    for(let i=0; i<values.length; i++)
        if(values[i] == "" || values[i] == undefined)
        {
            warnings.eq(i).css("display", "inline");
            returnValue = true;
        }
        else
            warnings.eq(i).css("display", "none");

    return returnValue;
}

function checkValues()
{
    const jobTitle = $("#title").val();
    const jobType = $("#type").val();
    const employmentStatus = $("input[name='status']:checked").val();
    const pros = $("#pros").val();
    const cons = $("#cons").val();
    const rating = $("input[name='rating']:checked").val();

    if(checkEmtpyValues(title, pros, cons, rating))
        return;
    
    let endpoint = "../api/add_review.php";
    let bearerToken = getCookie("jwt");
    let params = {
        "company_id": companyId,
        "job_title": jobTitle,
        "job_type": jobType,
        "employment_status": employmentStatus,
        "pros": pros,
        "cons": cons,
        "rating": rating
    };

    if($("#edit-mode").length > 0)
    {
        params["review_id"] = reviewId;
        endpoint = "../api/edit_review.php";
    }

    $.ajax({
        url: endpoint,
        method: "POST",
        data: JSON.stringify(params),
        contentType: "application/x-www-form-urlencoded",
        beforeSend: function(xmlhttp) {
            xmlhttp.setRequestHeader("Authorization", "Bearer " + bearerToken);
        },        
        success: function() {
            location.reload();
        }
    });
}

function fillData()
{
    $.ajax({
        url: "../api/get_review.php",
        method: "GET",
        data: {"id": reviewId},
        contentType: "application/x-www-form-urlencoded",      
        success: function(response) {
            $("#title").val(response["job_title"]);
            $("#type").val(response["job_type"]);
            $("input[name=status][value=" + response["employment_status"] + "]").prop("checked", true);
            $("#pros").val(response["pros"]);
            $("#cons").val(response["cons"]);            
            $("#rate-" + response["rating"]).prop("checked", true);
        }
    });
}