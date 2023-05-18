$(document).ready(function() {
    //load candidates
    const params = new Proxy(new URLSearchParams(window.location.search), {
        get: (searchParams, prop) => searchParams.get(prop),
    });
    const id = params.id;
    const years = params.minimum_years != "" ? params.minimum_years : "0";
    const sort = params.sort != "" ? (params.sort == "true") : false;
    const jobHoppers = params.hide_job_hoppers != "" ? (params.hide_job_hoppers == "true") : false;
    const descriptions = params.hide_no_descriptions != "" ? (params.hide_no_descriptions == "true") : false;
    const showIgnored = params.show_ignored != "" ? (params.show_ignored == "true") : false;

    jobId = params.id;

    $("#minimum-years").val(years);
    $("input[name='sort']").prop("checked", sort);
    $("input[name='job-hoppers']").prop("checked", jobHoppers);
    $("input[name='descriptions']").prop("checked", descriptions);
    $("input[name='show-ignored']").prop("checked", showIgnored);

    const bearerToken = getCookie("jwt");
    $.ajax({
        url: "../api/get_applicants.php",
        method: "GET",
        data: {"job_id": id},
        contentType: "application/x-www-form-urlencoded",
        dataType: "json",
        beforeSend: function(xmlhttp) {
            xmlhttp.setRequestHeader("Authorization", "Bearer " + bearerToken);
        },
        success: function(response) {
            $(".filler-row").remove();

            if(response["data"].length == 0)
            {
                $(".candidates-list").remove();
                $(".candidates-list").append("<i>Nobody has applied to this job yet.</i>");
                return;
            }
            
            let allCandidateRows = [];
            let loadedProfiles = 0;
            response["data"].forEach((candidate, _) => {
                let candidateRow = $("<tr></tr>");
                let buttonText = candidate["hidden"] ? "Unignore" : "Hide";

                if(!showIgnored && candidate["hidden"] == 1)
                {
                    loadedProfiles++;
                    return;
                }
                else if(showIgnored && candidate["hidden"] == 1)
                {
                    candidateRow.css("background-color", "#f48b86");
                }

                candidateRow.append(`<td class='candidate-profile'>
                    <img class='candidate-picture' src='${candidate["profile_picture"]}' alt='Candidate picture'><br/>
                    <a href='../views/candidates.php?id=${candidate["id"]}'>${candidate["first_name"]} ${candidate["last_name"]}</a>
                </td>`);

                //buttons
                let buttonsCell = $("<td></td>");
                let buttons = $("<div class='buttons'></div>");
                buttons.append(`<button class='search-button'>${buttonText}</button>`);
                buttons.append("<button class='search-button'>E-mail</button>");
                
                if(candidate["question1_answer"] || candidate["question2_answer"] || candidate["question3_answer"])
                    buttons.append(`<button class='search-button' onclick='toggleModal(${candidate["id"]}, 3)'>Answers</button>`);

                buttonsCell.append(buttons);

                //question answers
                answers[candidate["id"]] = [candidate["question1_answer"], candidate["question2_answer"], candidate["question3_answer"]];

                new Promise((resolve) => {
                    $.ajax({
                        url: "../api/get_experience_data.php",
                        method: "GET",
                        data: {"id": candidate["id"]},
                        contentType: "application/x-www-form-urlencoded",
                        dataType: "json",
                        success: function(experience) {
                            loadedProfiles++;

                            if(experience["years"] < years)
                                return;
                            if(jobHoppers && experience["average_employment_period"] < 6)
                                return;
                            if(descriptions && !experience["adds_descriptions"])
                                return;

                            //longest held positions;
                            let list = "<ul style='list-style: none; padding: 0;'>";
                            experience["all_experience"].forEach((job) => {
                                const years = Math.floor(job["work_period"] / 12);
                                const months = job["work_period"] % 12;
                                const company = job["company_id"] != null ? `<a href='../views/companies.php?id=${job["company_id"]}'>${job["company_name"]}</a></p>` : job["company_name"];
                                
                                let text = "<b>";
                                if(years > 0)
                                    text += years + " years ";
                                if(years > 0 && months > 0)
                                    text += "and "
                                if(months > 0)
                                    text += months + " months ";
                                text += `</b> as ${job["title"]} @ ${company}`;
                                list += `<li>${text}</li>`;
                            });
                            candidateRow.append("<td>" + list + "</ul></td>");

                            //insights text
                            let text = `<ul style='list-style: none; padding: 0;'>
                            <li><i class='fa-regular fa-calendar-days fa-fw'></i>Has a total of 
                            <b>${experience["years"]} years and ${experience["months"]} months</b> 
                            of work experience</li>`;

                            text += "<li><i class='fa-solid fa-business-time fa-fw'></i><b>";
                            if(experience["average_employment_period"] >= 6)
                                text += "Is likely to stay</b> at one workplace for a longer period of time</li>";
                            else
                                text += "Not likely to stay</b> at one workplace for long periods of time, might be a job hopper</li>";

                            text += "<li><i class='fa-solid fa-pencil fa-fw'></i><b>";
                            if(experience["adds_descriptions"])
                                text += "Provides descriptions</b> for most of their previous roles</li>";
                            else
                                text += "Doesn't provide descriptions</b> for most of their previous roles</li>";
                            
                            candidateRow.append("<td>" + text + "</ul></td>");
                            candidateRow.append(buttonsCell);
                            
                            allCandidateRows.push({row: candidateRow, total_months: experience["total_months"]});

                            resolve(allCandidateRows);
                        }
                    });
                }).then((result) => {
                    if(loadedProfiles == response["data"].length)
                    {
                        if(sort)
                            result.sort((a, b) => b.total_months - a.total_months);

                        result.forEach((object) => $("table").append(object.row));
                    }
                });
            });
        }
    });

    //get job title
    $.ajax({
        url: "../views/jobs.php?id=" + jobId,
        async: true,
        success: function(data) {
            const matches = data.match(/<title>(.*?)<\/title>/);
            const title = matches[0].split(">")[1].split("<")[0];
            $("#main").prepend(`<h1>Applicants for <a href="../views/jobs.php?id=${jobId}">${title}</a></h1>`);
        }
    });
});

let jobId = -1;
let answers = {};

function getJobQuestions(id)
{
    return new Promise((resolve, reject) => {
        $.ajax({
            url: "../api/get_job.php",
            method: "GET",
            data: {"id": jobId},
            contentType: "application/x-www-form-urlencoded",
            dataType: "json",
            success: function(response) {
                let string = "";
                for(let i=1; i<=3; i++)
                    if(response["question" + i] != null)
                    {
                        string += "<div>";
                        string += "<p><b>" + response["question" + i] + "</b></p>";
                        string += "<p>" + answers[id][i - 1] + "</p>";
                        string += "</div>";
                    }

                resolve(string);
            },
            error: function() {
                reject();
            }
        });
    });
}

//helper functions
function redirect(id)
{
    window.location.href = `../views/candidates.php?id=${id}`;
}

function applyFilters(id)
{
    const minimumYears = $("#minimum-years").val();
    const sort = $("#sort").prop("checked");
    const jobHoppers = $("#job-hoppers").prop("checked");
    const descriptions = $("#descriptions").prop("checked");
    const showIgnored = $("#show-ignored").prop("checked");
    
    let params = {
        minimum_years: minimumYears,
        sort: sort,
        hide_job_hoppers: jobHoppers,
        hide_no_descriptions: descriptions,
        show_ignored: showIgnored
    };
    params = new URLSearchParams(
        Object.fromEntries(Object.entries(params))
    );

    window.location.href = "index.php?id=" + id + "&" + params.toString();
}

async function toggleModal(id, action)
{
    if($(".modal").eq(0).css("display") == "none")
    {
        let data = null;
        if(action == 0)
            data = null; //hide applicant popup
        else if(action == 1)
            data = null; //unignore applicant popup
        else if(action == 2)
            data = null; //send email popup
        else
            data = await getJobQuestions(id);
        
        $(".modal-wrapper").empty();
        $(".modal-wrapper").append($(data));
        $(".modal-wrapper").append("<button class='search-button' onclick='toggleModal(null)'>Close</button>");

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