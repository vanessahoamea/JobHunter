function showJobs(index)
{
    $(".top-buttons").children().eq(index).addClass("selected-button");
    $(".top-buttons").children().eq((index + 1) % 3).removeClass("selected-button");
    $(".top-buttons").children().eq((index + 2) % 3).removeClass("selected-button");
}

function toggleModal()
{
    if($(".modal").eq(0).css("display") == "none")
    {
        $(".modal-wrapper").empty();
        $(".modal-wrapper").append("<p>...</p>");
        $(".modal-wrapper").append("<button class='search-button' onclick='toggleModal()'>Close</button>");

        $(".modal").eq(0).css("display", "block");
        $(".modal-wrapper").css("display", "block");
    }
    else
    {
        $(".modal").eq(0).css("display", "none");
        $(".modal-wrapper").css("display", "none");
    }
}

$(window).click(function(event) {
    if(event.target == $(".modal")[0])
        toggleModal(null);
});