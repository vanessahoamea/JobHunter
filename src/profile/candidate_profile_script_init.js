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
            response = response["data"];

            let workPeriods = [];
            let jobDatas = [];
            let ids = [];
            for(let i=0; i<response.length; i++)
            {
                const id = response[i]["id"];
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
                ids.push(id);
            }

            fillSection("#experience-content", ids, workPeriods, jobDatas);
        },
        error: function() {
            fillSection("#experience-content", null, null, null);
        }
    });

    //education section
    fillSection("#education-content", null, null, null);

    //projects section
    fillSection("#projects-content", null, null, null);

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