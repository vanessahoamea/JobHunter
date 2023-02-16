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
    $.ajax({
        url: "../api/get_about.php",
        method: "GET",
        data: {"id": id},
        contentType: "application/x-www-form-urlencoded",
        dataType: "json",
        success: function(response) {
            if(response["text"] != null)
            {
                $("#about-content").text(response["text"]);
                $("#about").val(response["text"]);
            }
            else
                fillSection("#about-content", null, null);
        },
        error: function() {
            fillSection("#about-content", null, null);
        }
    });

    //experience section
    $.ajax({
        url: "../api/get_experience.php",
        method: "GET",
        data: {"id": id},
        contentType: "application/x-www-form-urlencoded",
        dataType: "json",
        success: function(response) {
            response = response["data"];

            let workPeriods = [];
            let jobDatas = [];
            let ids = [];
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
                ids.push(response[i]["id"]);
            }

            fillSection("#experience-content", ids, workPeriods, jobDatas);
        },
        error: function() {
            fillSection("#experience-content", null, null, null);
        }
    });

    //education section
    $.ajax({
        url: "../api/get_education.php",
        method: "GET",
        data: {"id": id},
        contentType: "application/x-www-form-urlencoded",
        dataType: "json",
        success: function(response) {
            response = response["data"];

            let studyPeriods = [];
            let educationDatas = [];
            let ids = [];
            for(let i=0; i<response.length; i++)
            {
                const studyStart = response[i]["start_month"] + " " + response[i]["start_year"];
                const studyEnd = response[i]["end_month"] != null ? response[i]["end_month"] + " " + response[i]["end_year"] : "Present";
                const studyPeriod = studyStart + " - " + studyEnd;

                let educationData = "<p class='bigger-text'>" + response[i]["institution_name"] + "</p>";
                if(response[i]["degree"] != null)
                    educationData += "<p>Degree: " + response[i]["degree"] + "</p>";
                if(response[i]["study_field"] != null)
                    educationData += "<p>Field of study: " + response[i]["study_field"] + "</p>";
                if(response[i]["description"] != null)
                    educationData += "<p>" + response[i]["description"] + "</p>";
                
                studyPeriods.push(studyPeriod);
                educationDatas.push(educationData);
                ids.push(response[i]["id"]);
            }

            fillSection("#education-content", ids, studyPeriods, educationDatas);
        },
        error: function() {
            fillSection("#education-content", null, null, null);
        }
    });

    //projects section
    $.ajax({
        url: "../api/get_projects.php",
        method: "GET",
        data: {"id": id},
        contentType: "application/x-www-form-urlencoded",
        dataType: "json",
        success: function(response) {
            response = response["data"];

            let projectPeriods = [];
            let projectDatas = [];
            let ids = [];
            for(let i=0; i<response.length; i++)
            {
                const projectStart = response[i]["start_month"] + " " + response[i]["start_year"];
                const projectEnd = response[i]["end_month"] != null ? response[i]["end_month"] + " " + response[i]["end_year"] : "Present";
                const projectPeriod = projectStart + " - " + projectEnd;

                let projectData = "<p class='bigger-text'>" + response[i]["project_name"] + "</p>";
                if(response[i]["project_link"] != null)
                    projectData += "<a href='" + response[i]["project_link"] + "'>Link to project</a>";
                if(response[i]["description"] != null)
                    projectData += "<p>" + response[i]["description"] + "</p>";
                
                projectPeriods.push(projectPeriod);
                projectDatas.push(projectData);
                ids.push(response[i]["id"]);
            }

            fillSection("#projects-content", ids, projectPeriods, projectDatas);
        },
        error: function() {
            fillSection("#projects-content", null, null, null);
        }
    });

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
    }

    //autocomplete company name search
    let selectedName = null;
    $("#company-name").focus(function() {
        if($("#company-name").val() != "")
            selectedName = $("#company-name").val();
    });

    $("#company-name").on("input", function() {
        let query = $(this).val();
        let container = $("#listing-container");

        if(selectedName != null && selectedName != query)
            $("#company-id").val("");

        container.empty();

        if(query == "")
            container.css("display", "none");
        else
        {
            fetch(`../api/get_company_names.php?company_name=${query}`)
            .then(response => response.json())
            .then(response => {
                response = response["data"];

                container.css("display", "block");
                for(let i=0; i<response.length; i++)
                {
                    container.append("<li class='listing'>" + response[i]["company_name"] + "</li>");
                    container.append("<p style='display: none;'>" + response[i]["id"] + "</p>");
                }
            })
            .catch();
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

        if($("#self-view").length != 0)
        {
            const containers = $(target).children(".data-container");
            const buttons = $("<div class='buttons'></div>");
            buttons.append("<button class='section-button' onclick='getId(this, 0)'><i class='fa-solid fa-pen-to-square fa-fw'></i>edit</button>");
            buttons.append("<button class='section-button' onclick='getId(this, 1)'><i class='fa-solid fa-trash fa-fw'></i>delete</button>");
            buttons.insertAfter(containers);
        }
    }
    else
        $(target).append("<p><i>This section is empty.</i></p>");
}