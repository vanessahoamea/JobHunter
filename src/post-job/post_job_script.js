$(document).ready(function() {
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
            
            e.target.value = ""; //reset input
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
                    container.css("display", "block");
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

    //description text options
    $(".option-button").on("click", function() {
        document.execCommand(this.id, false, null);
    });
});

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

//add new job to the database
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

    if(checkEmtpyValues(title, locationCoords, description))
        return;
    
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
        "description": encodeURIComponent(description).replace(/%20/g, "+")
    };

    $.ajax({
        url: "../api/add_job.php",
        method: "POST",
        data: JSON.stringify(params),
        contentType: "application/x-www-form-urlencoded",
        beforeSend: function(xmlhttp) {
            xmlhttp.setRequestHeader("Authorization", "Bearer " + bearerToken);
        },        
        success: function() {
            location.reload();
        },
        error: function(xmlhttp) {
            console.log(xmlhttp.responseText);
        }
    });
}