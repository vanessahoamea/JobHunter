//constants and variables
const modal = $(".modal").eq(0);
const modals = $(".modal-wrapper");

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

    if(currentIndex > 0 && currentIndex < 4)
        $("form")[currentIndex].reset();
    $(".warning-text").hide();

    modals.eq(1).children("button").text("Add experience");
    modals.eq(2).children("button").text("Add education");
    modals.eq(3).children("button").text("Add project");

    //resetting selected item index (for editing/deleting)
    currentItemId = -1;
}

//hide end date selects if job is ongoing
function toggleEndDate(index, category)
{
    if($(`#${category}-ongoing`).is(":checked"))
    {
        $(`div[name='${category}-end-date']`).addClass("disabled");
        $(`#${category}-end-month`).prop("disabled", true);
        $(".year-list").eq(index).prop("disabled", true);
    }
    else
    {
        $(`div[name='${category}-end-date']`).removeClass("disabled");
        $(`#${category}-end-month`).prop("disabled", false);
        $(".year-list").eq(index).prop("disabled", false);
    }
}

//add or edit user information
function checkEmtpyValues(values, startIndex)
{
    const warnings = $(".warning-text");
    let returnValue = false;

    for(let i=0; i<=values.length; i++)
        if(values[i] == "")
        {
            warnings.eq(i + startIndex).css("display", "inline");
            returnValue = true;
        }
        else
            warnings.eq(i).css("display", "none");

    return returnValue;
}

function apiRequest(endpoint, method, params)
{
    $.ajax({
        url: endpoint,
        method: method,
        data: JSON.stringify(params),
        contentType: "application/x-www-form-urlencoded",
        beforeSend: function(xmlhttp) {
            xmlhttp.setRequestHeader("Authorization", "Bearer " + getCookie("jwt"));
        },        
        success: function() {
            hideModal();
            location.reload();
        }
    });
}

function updateAbout()
{
    const text = $("#about").val();
    const portfolioLink1 = $("#portfolio-link1").val();
    const portfolioLink2 = $("#portfolio-link2").val();
    const portfolioLink3 = $("#portfolio-link3").val();

    const endpoint = "../api/add_about.php";
    const params = {
        "text": text,
        "link1": portfolioLink1,
        "link2": portfolioLink2,
        "link3": portfolioLink3
    };

    apiRequest(endpoint, "POST", params);
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
    const ongoing = $("#job-ongoing").is(":checked");

    if(checkEmtpyValues([title, companyName], 0))
        return;

    let endpoint = "../api/add_experience.php";
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

    apiRequest(endpoint, "POST", params);
}

function addEducation()
{
    const institutionName = $("#institution-name").val();
    const startMonth = $("#education-start-month").val().slice(0, 3);
    const startYear = $(".year-list").eq(2).val();
    const endMonth = $("#education-end-month").val().slice(0, 3);
    const endYear =  $(".year-list").eq(3).val();
    const degree = $("#degree").val();
    const studyField = $("#study-field").val();
    const description = $("#education-description").val();
    const ongoing = $("#education-ongoing").is(":checked");

    if(checkEmtpyValues([institutionName], 2))
        return;

    let endpoint = "../api/add_education.php";
    let params = {
        "institution_name": institutionName,
        "start_month": startMonth,
        "start_year": startYear,
        "end_month": ongoing == true ? null : endMonth,
        "end_year": ongoing == true ? null : endYear,
        "degree": degree,
        "study_field": studyField,
        "description": description
    };

    if(currentItemId != -1)
    {
        params["education_id"] = currentItemId;
        params["ongoing"] = ongoing;
        endpoint = "../api/edit_education.php";
    }

    apiRequest(endpoint, "POST", params);
}

function addProject()
{
    const projectName = $("#project-name").val();
    const startMonth = $("#project-start-month").val().slice(0, 3);
    const startYear = $(".year-list").eq(4).val();
    const endMonth = $("#project-end-month").val().slice(0, 3);
    const endYear =  $(".year-list").eq(5).val();
    const projectLink = $("#project-link").val();
    const description = $("#project-description").val();
    const ongoing = $("#project-ongoing").is(":checked");

    if(checkEmtpyValues([projectName], 3))
        return;

    let endpoint = "../api/add_project.php";
    let params = {
        "project_name": projectName,
        "start_month": startMonth,
        "start_year": startYear,
        "end_month": ongoing == true ? null : endMonth,
        "end_year": ongoing == true ? null : endYear,
        "project_link": projectLink,
        "description": description
    };

    if(currentItemId != -1)
    {
        params["project_id"] = currentItemId;
        params["ongoing"] = ongoing;
        endpoint = "../api/edit_project.php";
    }

    apiRequest(endpoint, "POST", params);
}

//edit + delete item
function setDate(data, startMonth, startYear, endMonth, endYear, category)
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
        $(`#${category}-ongoing`).prop("checked", true);
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
                setDate(leftContent, $("#job-start-month"), $(".year-list").eq(0), $("#job-end-month"), $(".year-list").eq(1), "job");
                toggleEndDate(1, "job");
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
                setDate(leftContent, $("#education-start-month"), $(".year-list").eq(2), $("#education-end-month"), $(".year-list").eq(3), "education");
                toggleEndDate(3, "education");
                $("#institution-name").val(rightContent.eq(0).text());
                if(rightContent.length > 1)
                {
                    rightContent.map((index, child) => {
                        if(index == 0)
                            return;
                        else if($(child).text().split(": ")[0] == "Degree")
                            $("#degree").val($(child).text().split(":")[1]);
                        else if($(child).text().split(": ")[0] == "Field of study")
                            $("#study-field").val($(child).text().split(":")[1]);
                        else
                            $("#education-description").val($(child).text());
                    });
                }

                break;
            }
            case 3:
            {
                setDate(leftContent, $("#project-start-month"), $(".year-list").eq(4), $("#project-end-month"), $(".year-list").eq(5), "project");
                toggleEndDate(5, "project");
                $("#project-name").val(rightContent.eq(0).text());
                if(rightContent.length > 1)
                {
                    rightContent.map((index, child) => {
                        if(index == 0)
                            return;
                        else if($(child).is("a"))
                            $("#project-link").val($(child).attr("href"));
                        else
                            $("#project-description").val($(child).text());
                    });
                }
                
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
    let params = {};

    if(currentItemSection == "experience-content")
    {
        endpoint += "remove_experience.php";
        params = {"experience_id": currentItemId};
    }
    else if(currentItemSection == "education-content")
    {
        endpoint += "remove_education.php";
        params = {"education_id": currentItemId};
    }
    else if(currentItemSection == "projects-content")
    {
        endpoint += "remove_project.php";
        params = {"project_id": currentItemId};
    }

    apiRequest(endpoint, "DELETE", params);
}