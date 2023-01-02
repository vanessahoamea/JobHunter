//constants and variables
const modal = $(".modal").eq(0);
const modals = $(".modal-wrapper");
const forms = $("form");

let currentIndex = -1;
let currentItemId = -1;
let currentItemSection = null;

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

function hideModal()
{
    modals.eq(currentIndex).css("display", "none");
    modal.css("display", "none");

    //resetting forms
    if(currentIndex < 4)
        forms[currentIndex].reset();
    $(".warning-text").hide();

    modals.eq(1).children("button").text("Add experience");
    modals.eq(2).children("button").text("Add education");
    modals.eq(3).children("button").text("Add project");

    //resetting selected item index (for editing/deleting)
    currentItemId = -1;
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
    const startYear = $(".year-list").eq(0).val();
    const endMonth = $("#job-end-month").val().slice(0, 3);
    const endYear =  $(".year-list").eq(1).val();
    const description = $("#job-description").val();
    const ongoing = $("#ongoing").is(":checked");

    if(checkEmtpyValues(title, companyName))
        return;

    let endpoint = "../api/add_experience.php";
    let bearerToken = getCookie("jwt");
    let params = {
        "title": title,
        "company_id": companyId == "" ? null : companyId,
        "company_name": companyName,
        "type": jobType,
        "start_month": startMonth,
        "start_year": startYear,
        "end_month": ongoing == true ? null : endMonth,
        "end_year": ongoing == true ? null : endYear,
        "description": description
    };

    if(currentItemId != -1)
    {
        params["experience_id"] = currentItemId;
        params["ongoing"] = ongoing;
        endpoint = "../api/edit_experience.php";
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
            hideModal();
            location.reload();
        }
    });
}

//edit + delete item
function setDate(data, startMonth, startYear, endMonth, endYear)
{
    originalStartMonth = data.split(" - ")[0].split(" ")[0];
    originalStartYear = data.split(" - ")[0].split(" ")[1];
    originalEndMonth = data.split(" - ")[1].split(" ")[0];
    originalEndYear = data.split(" - ")[1].split(" ")[1];

    startYear.val(originalStartYear);
    startMonth.children().each(function() {
        if($(this).text().slice(0, 3) == originalStartMonth)
        {
            startMonth.val($(this).text());
            return;
        }
    });

    if(originalEndMonth != "Present")
    {
        endMonth.parent().removeClass("disabled");
        endYear.val(originalEndYear);
        endMonth.children().each(function() {
            if($(this).text().slice(0, 3) == originalEndMonth)
            {
                endMonth.val($(this).text());
                return;
            }
        });
    }
    else
    {
        endMonth.parent().addClass("disabled");
        $("#ongoing").prop("checked", true);
    }
}

function getId(target, action)
{
    const currentItemContainer = $(target).parent().prev();
    currentItemSection = currentItemContainer.parent().attr("id");
    currentItemId = currentItemContainer.children(".item-id").text();

    if(action == 0)
    {
        if(currentItemSection == "experience-content")
            currentIndex = 1;
        else if(currentItemSection == "education-content")
            currentIndex = 2;
        else if(currentItemSection == "projects-content")
            currentIndex = 3;

        const leftContent = currentItemContainer.children(".left-data").eq(0).text();
        const rightContent = currentItemContainer.children(".right-data").children(".right-data-container").children();

        switch(currentIndex)
        {
            case 1:
            {
                setDate(leftContent, $("#job-start-month"), $(".year-list").eq(0), $("#job-end-month"), $(".year-list").eq(1));
                $("#job-title").val(rightContent.eq(0).text());
                $("#job-type").val(rightContent.eq(1).text().split(" @ ")[0]);
                $("#company-name").val(rightContent.eq(1).text().split(" @ ")[1]);
                $("#company-id").val("");
                $("#job-description").val("");
                if(rightContent.eq(1).children().length == 1)
                    $("#company-id").val(rightContent.eq(1).children().eq(0).attr("href").split("=")[1]);
                if(rightContent.length == 3)
                    $("#job-description").val(rightContent.eq(2).text());
                                
                break;
            }
            case 2:
            {
                break;
            }
            case 3:
            {
                break;
            }
        }

        modals.eq(currentIndex).children("button").text("Submit changes");
        showModal(currentIndex);
    }
    else
        showModal(4);
}

function deleteItem()
{
    let endpoint = "../api/";
    let bearerToken = getCookie("jwt");

    if(currentItemSection == "experience-content")
        endpoint += "remove_experience.php"
    else if(currentItemSection == "education-content")
        endpoint += "remove_education.php"
    else if(currentItemSection == "projects-content")
        endpoint += "remove_project.php"
    
    $.ajax({
        url: endpoint,
        method: "DELETE",
        data: JSON.stringify({"experience_id": currentItemId}),
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

//render data on page
function fillSection(target, id, leftContent, rightContent)
{
    $(target).empty();

    if(leftContent != null && rightContent != null)
    {
        for(let i=0; i<leftContent.length; i++)
        {
            const div = $("<div class='data-container'></div>");
            div.append("<div class='item-id' style='display: none;'>" + id[i] + "</div>");
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
        buttons.append("<button class='section-button' onclick='getId(this, 0)'><i class='fa-solid fa-pen-to-square fa-fw'></i>edit</button>");
        buttons.append("<button class='section-button' onclick='getId(this, 1)'><i class='fa-solid fa-trash fa-fw'></i>delete</button>");
        buttons.insertAfter(containers);
    }
}