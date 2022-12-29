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

    $(document).on("click", "#main", function () {
        $("#listing-container").css("display", "none");
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
function checkValues()
{    
    console.log($("#location-lat").val(), $("#location-lon").val());
}