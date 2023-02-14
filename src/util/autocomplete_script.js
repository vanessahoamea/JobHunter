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
    else if(text.length >= 2)
    {
        fetch(`https://api.geoapify.com/v1/geocode/autocomplete?text=${text}&format=json&apiKey=${apiKey}`)
        .then(response => response.json())
        .then(response => {
            response = response["results"];
            if(response.length > 0)
            {
                container.empty();
                container.css("display", "flex");
                container.css("flex-direction", "column");
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