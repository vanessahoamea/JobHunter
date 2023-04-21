$(document).ready(function() {
    //fill in job data if in edit mode
    if($("#edit-mode").length > 0)
    {
        const params = new Proxy(new URLSearchParams(window.location.search), {
            get: (searchParams, prop) => searchParams.get(prop),
        });
        jobId = atob(atob(params.item).split(".")[0]);

        fillData();
        $(".submit-button").text("Submit changes");
    }

    //description text options
    $(".option-button").on("click", function() {
        document.execCommand(this.id, false, null);
    });
});

let jobId = -1;

//add new job to the database
function checkEmtpyValues(title, locationCoords, description)
{
    const values = [title, locationCoords, description];
    const warnings = $(".warning-text");
    let returnValue = false;

    for(let i=0; i<values.length; i++)
        if(values[i] == "" || (values[i][0] == "" || values[i][1] == ""))
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
    const title = $("#title").val();
    const skills = skillsArray;
    const type = $("#type").val();
    const level = $("#level").val();
    const locationName = $("#location").val();
    const locationCoords = [$("#location-lat").val(), $("#location-lon").val()];
    const salary = $("#salary").val();
    const physical = $("input[name=physical]:checked").eq(0).val();
    const description = $("#description").html();
    const question1 = $("#question1").val();
    const question2 = $("#question2").val();
    const question3 = $("#question3").val();

    if(checkEmtpyValues(title, locationCoords, description))
        return;
    
    let endpoint = "../api/add_job.php";
    let bearerToken = getCookie("jwt");
    let params = {
        "title": title,
        "skills": skills,
        "type": type,
        "level": level,
        "location_name": locationName,
        "location_coords": locationCoords,
        "salary": salary,
        "physical": physical,
        "description": encodeURIComponent(description).replace(/%20/g, "+"),
        "question1": question1,
        "question2": question2,
        "question3": question3,
    };

    if($("#edit-mode").length > 0)
    {
        params["job_id"] = jobId;
        endpoint = "../api/edit_job.php";
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
            window.location.href = "../profile";
        }
    });
}

//edit job
function fillData()
{
    $.ajax({
        url: "../api/get_job.php",
        method: "GET",
        data: {"id": jobId},
        contentType: "application/x-www-form-urlencoded",      
        success: function(response) {
            $("#title").val(response["title"]);
            $("#type").val(response["type"]);
            $("#level").val(response["level"]);
            $("#location").val(response["location_name"]);
            $("#location-lat").val(JSON.parse(response["location_coords"])[0]);
            $("#location-lon").val(JSON.parse(response["location_coords"])[1]);
            $("input[name=physical][value=" + response["physical"] + "]").prop("checked", true);
            $("#description").html(response["description"]);
            $("#question1").val(response["question1"]);
            $("#question2").val(response["question2"]);
            $("#question3").val(response["question3"]);

            skillsArray = JSON.parse(response["skills"]);
            skillsArray.forEach(skill => {
                const listItem = $("<li><i class='fa-solid fa-xmark fa-fw' onclick='removeSkill(this)'></i>" + skill + "</li>");
                $(listItem).insertBefore("#skills");
            });

            if(response["salary"] != null)
                $("#salary").val(response["salary"]);
        }
    });
}