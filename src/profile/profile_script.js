$(document).ready(function() {
    //populate date dropdown
    const yearList = $(".year-list");

    for(index=0; index<yearList.length; index++)
    {
        let currentYear = new Date().getFullYear();
        let earliestYear = 1970;
        while(currentYear >= earliestYear)
        {
            yearList.eq(index).append("<option value='" + currentYear + "'>" + currentYear + "</option>");
            currentYear -= 1;
        }
    }

    //autocomplete company name search
    let selectedName = null;

    $("#company-name").keyup(function() {
        let query = $(this).val();

        if(selectedName != null && selectedName != query)
            $("#company-id").val("-1");

        container = $("#listing-container");
        container.empty();

        if(query == "")
            container.css("display", "none");
        else
        {
            $.ajax({
                url: "../api/find_company_names.php",
                method: "POST",
                data: JSON.stringify({"company_name": query}),
                contentType: "application/x-www-form-urlencoded",
                dataType: "json",
                success: function(response) {
                    response = response[0];
                    container.css("display", "block");
                    
                    for(let i=0; i<response.length; i++)
                    {
                        container.append("<li class='listing'>" + response[i]["company_name"] + "</li>");
                        container.append("<p style='display: none;'>" + response[i]["id"] + "</p>");
                    }
                }
            });
        }
    });

    $(document).on("click", ".listing", function() {
        $("#company-name").val($(this).text());
        $("#company-id").val($(this).next().text());
        $("#listing-container").css("display", "none");
        selectedName = $(this).text();
    });

    $(document).on("click", ".modal-wrapper", function () {
        $("#listing-container").css("display", "none");
    });
});

//constants and variables
const modal = $(".modal").eq(0);
const modals = $(".modal-wrapper");
const forms = $("form");
let currentIndex = -1;

//handle modals
$(window).click(function(event) {
    if(event.target == $(".modal")[0])
        hideModal();
});

function showModal(index)
{
    modal.css("display", "block");
    for(let i=0; i<modals.length; i++)
        modals.eq(i).css("display", "none");
    
    modals.eq(index).css("display", "flex");
    currentIndex = index;
}

function hideModal() {
    modals.eq(currentIndex).css("display", "none");
    modal.css("display", "none");

    forms[currentIndex].reset();
    $(".warning-text").hide();
}

//add new experience
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

function checkEmtpyValues(title, companyName)
{
    const values = [title, companyName];
    const warnings = $(".warning-text");
    let returnValue = false;

    for(let i=0; i<values.length; i++)
        if(values[i] == "")
        {
            warnings.eq(i).css("display", "inline");
            returnValue = true;
        }
        else
            warnings.eq(i).css("display", "none");

    return returnValue;
}

function addExperience()
{
    const title = $("#job-title").val();
    const companyId = $("#company-id").val();
    const companyName = $("#company-name").val();
    const jobType = $("#job-type").val();
    const startMonth = $("#job-start-month").val();
    const startYear = $(".year-list")[0].value;
    const endMonth = $("#job-end-month").val();
    const endYear =  $(".year-list")[1].value;
    const description = $("#job-description").val();

    if(checkEmtpyValues(title, companyName))
        return;

    let bearerToken = getCookie("jwt");
    let params = {
        "title": title,
        "company_id": companyId == "-1" ? null : companyId,
        "company_name": companyName,
        "type": jobType,
        "start_month": startMonth,
        "start_year": startYear,
        "end_month": endMonth,
        "end_year": endYear,
        "description": description
    };

    $.ajax({
        url: "../api/add_experience.php",
        method: "POST",
        data: JSON.stringify(params),
        contentType: "application/x-www-form-urlencoded",
        beforeSend: function(xmlhttp) {
            xmlhttp.setRequestHeader("Authorization", "Bearer " + bearerToken);
        },        
        success: function(response) {
            hideModal();
            location.reload();
        }
    });
}

//TODO: move to company page
function show_data()
{
    let bearerToken = getCookie("jwt");

    let xmlhttp = new XMLHttpRequest();
    xmlhttp.open("GET", "../api/experience_data.php", true);
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlhttp.setRequestHeader("Authorization", "Bearer " + bearerToken);
    xmlhttp.responseType = "json";

    xmlhttp.onload = function(e)
    {
        if(this.status == 200)
        {
            string = "Has a total of " + Math.floor(this.response["total_months"] / 12) + " years and " + (this.response["total_months"] % 12) + " months of work experience<br>";
            if(this.response["average_employment_period"] >= 6)
                string += "Is likely to stay at one workplace for a longer period of time<br>";
            else
                string += "Not likely to stay at one workplace for long periods of time, might be a job hopper<br>";
            if(this.response["adds_descriptions"])
                string += "Provides descriptions for most of their previous roles<br>";
            else
                string += "Doesn't provide descriptions for most of their previous roles<br>";

            $("#result-text").innerHTML = string;
        }
    };
    xmlhttp.send();
}