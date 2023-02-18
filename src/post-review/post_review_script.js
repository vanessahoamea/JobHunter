$(document).ready(function() {
    const params = new Proxy(new URLSearchParams(window.location.search), {
        get: (searchParams, prop) => searchParams.get(prop),
    });
    companyId = params.id;
});

let companyId = -1;

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

    $.ajax({
        url: endpoint,
        method: "POST",
        data: JSON.stringify(params),
        contentType: "application/x-www-form-urlencoded",
        beforeSend: function(xmlhttp) {
            xmlhttp.setRequestHeader("Authorization", "Bearer " + bearerToken);
        },        
        success: function() {
            window.location.href = "../views/reviews.php?id=" + companyId;
        },
        error: function(xmlhttp) {
            console.log(xmlhttp.responseText);
        }
    });
}