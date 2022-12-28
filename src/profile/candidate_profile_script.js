$(document).ready(function() {
    //replace placeholder skeleton with text and images
    const params = new Proxy(new URLSearchParams(window.location.search), {
        get: (searchParams, prop) => searchParams.get(prop),
    });
    const id = params.id;
    
    //profile section
    $.ajax({
        url: "../api/get_candidate.php",
        method: "GET",
        data: {"id": id},
        contentType: "application/x-www-form-urlencoded",
        dataType: "json",
        success: function(response) {
            $("#full-name").text(response["first_name"] + " " + response["last_name"]);

            $(".information-list").empty();
            $(".information-list").append("<li><i class='fa-solid fa-envelope fa-fw'></i>" + response["email"] + "</li>");
            if(response["phone"] != null)
                $(".information-list").append("<li><i class='fa-solid fa-phone fa-fw'></i>" + response["phone"] + "</li>");
        }
    });

    //about section
    fillSection("#about-content", null, null);

    //experience section
    $.ajax({
        url: "../api/get_experience.php",
        method: "GET",
        data: {"id": id},
        contentType: "application/x-www-form-urlencoded",
        dataType: "json",
        success: function(response) {            
            let workPeriods = [];
            let jobDatas = [];
            for(let i=0; i<response.length; i++)
            {
                const jobStart = response[i]["start_month"] + " " + response[i]["start_year"];
                const jobEnd = response[i]["end_month"] != null ? response[i]["end_month"] + " " + response[i]["end_year"] : "Present";
                const workPeriod = jobStart + " - " + jobEnd;

                const company = response[i]["company_id"] ? "<a href='companies.php?id=" + response[i]["company_id"] + "'>" + response[i]["company_name"] + "</a>" : response[i]["company_name"];
                let jobData = "<p class='bigger-text'>" + response[i]["title"] + "</p>";
                jobData += "<p>" + response[i]["type"] + " @ " + company + "</p>";
                if(response[i]["description"] != null)
                    jobData += "<p>" + response[i]["description"] + "</p>";
                
                workPeriods.push(workPeriod);
                jobDatas.push(jobData);
            }

            fillSection("#experience-content", workPeriods, jobDatas);
        },
        error: function() {
            fillSection("#experience-content", null, null);
        }
    });

    //education section
    fillSection("#education-content", null, null);

    //projects section
    fillSection("#projects-content", null, null);

    //populate date dropdown
    const yearList = $(".year-list");
    for(let index=0; index<yearList.length; index++)
    {
        let currentYear = new Date().getFullYear();
        let earliestYear = 1970;
        while(currentYear >= earliestYear)
        {
            yearList.eq(index).append("<option value='" + currentYear + "'>" + currentYear + "</option>");
            currentYear -= 1;
        }

        yearList.eq(index).css("margin-top", "0");
        yearList.eq(index).prev().css("margin-bottom", "0");
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
                url: "../api/get_company_names.php",
                method: "GET",
                data: {"company_name": query},
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

//hide end date selects if job is ongoing
function toggleEndDate()
{
    let month = $("#job-end-month");
    let year = $(".year-list").eq(1);

    $("div[name='job-end-date']").toggleClass("disabled");

    if($("#ongoing").is(":checked"))
    {
        month.prop("disabled", true);
        year.prop("disabled", true);
    }
    else
    {
        month.prop("disabled", false);
        year.prop("disabled", false);
    }
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
    const startMonth = $("#job-start-month").val().slice(0, 3);
    const startYear = $(".year-list")[0].value;
    const endMonth = $("#job-end-month").val().slice(0, 3);
    const endYear =  $(".year-list")[1].value;
    const description = $("#job-description").val();
    const ongoing = $("#ongoing").is(":checked");

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
        "end_month": ongoing == true ? null : endMonth,
        "end_year": ongoing == true ? null : endYear,
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
        success: function() {
            hideModal();
            location.reload();
        }
    });
}

//helper functions
function fillSection(target, leftContent, rightContent)
{
    $(target).empty();

    if(leftContent != null && rightContent != null)
    {
        for(let i=0; i<leftContent.length; i++)
        {
            const div = $("<div class='data-container'></div>");
            div.append("<div class='experience-id' style='display: none;'></div>"); //for editing/deleting
            div.append("<div class='left-data bigger-text'>" + leftContent[i] + "</div>");
            div.append("<div class='right-data'><div class='right-data-container'>" + rightContent[i] + "</div></div>");
            $(target).append(div);
        }

        addSectionButtons(target);
    }
    else
        $(target).append("<p><i>This section is empty.</i></p>");

}

function addSectionButtons(target)
{
    if($("#self-view").length != 0)
    {
        const containers = $(target).children(".data-container");
        const buttons = $("<div class='buttons'></div>");
        buttons.append("<button class='section-button'><i class='fa-solid fa-pen-to-square'></i>edit</button>");
        buttons.append("<button class='section-button'><i class='fa-solid fa-trash'></i>delete</button>");
        buttons.insertAfter(containers);
    }
}