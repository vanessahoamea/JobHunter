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

    $.ajax({
        url: "../api/get_applicants.php",
        method: "GET",
        data: {"job_id": id},
        contentType: "application/x-www-form-urlencoded",
        dataType: "json",
        beforeSend: function(xmlhttp) {
            xmlhttp.setRequestHeader("Authorization", "Bearer " + getCookie("jwt"));
        },
        success: function(response) {
            $(".filler-row").remove();

            if(response["data"].length == 0)
            {
                $(".candidates-list").empty();
                $(".candidates-list").append("<i>Nobody has applied to this job yet.</i>");
                return;
            }
            
            let allCandidateRows = [];
            let loadedProfiles = 0;
            response["data"].forEach((candidate, _) => {
                let candidateRow = $("<tr></tr>");
                let buttonText = candidate["hidden"] ? "Unignore" : "Hide";

                candidateRow.append(`<div style='display: none;'>${candidate["hidden"]}</div>`);

                if(candidate["emailed"] == 1)
                    candidateRow.css("background-color", "#add8e6");

                //name and picture
                candidateRow.append(`<td class='candidate-profile'>
                    <img class='candidate-picture' src='${candidate["profile_picture"]}' alt='Candidate picture'><br/>
                    <a href='../views/candidates.php?id=${candidate["id"]}'>${candidate["first_name"]} ${candidate["last_name"]}</a>
                </td>`);

                //buttons
                let buttonsCell = $("<td></td>");
                let buttons = $("<div class='buttons'></div>");
                buttons.append(`<button class='search-button' onclick='toggleModal(${candidate["id"]}, 0, "${buttonText}")'>${buttonText}</button>`);
                buttons.append(`<button class='search-button' onclick='toggleModal(${candidate["id"]}, 1, null)'>E-mail</button>`);
                
                if(candidate["question1_answer"] || candidate["question2_answer"] || candidate["question3_answer"])
                    buttons.append(`<button class='search-button' onclick='toggleModal(${candidate["id"]}, 2, null)'>Answers</button>`);

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
                            if(experience["average_employment_period"] >= 12)
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

                        if(result.length == 0)
                        {
                            $(".candidates-list").empty();
                            $(".candidates-list").append("<i>Nobody has applied to this job yet.</i>");
                        }
                        result.forEach((object) => {
                            if($(object.row).children().eq(0).text() == "1")
                            {
                                if(!showIgnored)
                                    return;
                                else
                                    $(object.row).css("background-color", "#f48b86");
                            }
                            $("table").append(object.row);
                        });
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

//functions for API requests
function apiRequest(action, candidateId)
{
    const endpoint = action == 0 ? "edit_applicant_visibility.php" : "email_candidate.php";

    $.ajax({
        url: "../api/" + endpoint,
        method: "POST",
        data: JSON.stringify({
            "job_id": jobId,
            "candidate_id": candidateId,
            "when": $("#when").val(),
            "where": $("#where").val()
        }),
        contentType: "application/x-www-form-urlencoded",
        dataType: "json",
        beforeSend: function(xmlhttp) {
            xmlhttp.setRequestHeader("Authorization", "Bearer " + getCookie("jwt"));

            if(action == 1)
            {
                $(".modal-wrapper").empty();
                $(".modal-wrapper").append("<p>Sending e-mail...</p>");
                $(".modal-wrapper").append("<button class='search-button' onclick='toggleModal(null, null, null)'>Close</button>");
            }
        },
        success: function() {
            window.location.reload();
        }
    });
}

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

async function toggleModal(id, action, actionText)
{
    if($(".modal").eq(0).css("display") == "none")
    {
        $(".modal-wrapper").empty();

        if(action == 0)
        {
            $(".modal-wrapper").append(`<p>Are you sure you want to ${actionText.toLowerCase()} this candidate?</p>`);
            $(".modal-wrapper").append(`<button class='search-button' onclick='apiRequest(0, ${id})'>Confirm</button>`);
        }
        else if(action == 1)
        {
            $(".modal-wrapper").append("<p>We will send this candidate an email telling them they have earned an interview. Please provide a detailed time and place for your meeting.</p>");

            let div = $("<div style='text-align: left; margin-top: 15px;'></div>");
            div.append(`<label for='when'>Time of the interview (include the date, hour, etc.)</label>`);
            div.append(` <span class='warning-text'>(this field is required)</span>`);
            $(".modal-wrapper").append(div);
            $(".modal-wrapper").append(`<textarea name='when' id='when' class='input' rows=2>`);
            
            div = $("<div style='text-align: left;'></div>");
            div.append(`<label for='where'>Where the interview will take place (e.g. the company headquarters, a Zoom meeting, etc.)</label>`);
            div.append(` <span class='warning-text'>(this field is required)</span>`);
            $(".modal-wrapper").append(div);
            $(".modal-wrapper").append(`<textarea name='where' id='where' class='input' rows=2>`);

            $(".modal-wrapper").append(`<button class='search-button' onclick='checkAnswers(${id})'>Send</button>`);
        }
        else
        {
            data = await getJobQuestions(id);
            $(".modal-wrapper").append(data);
            $(".modal-wrapper").append(`<button class='search-button' onclick='toggleModal(null, null, null)'>Close</button>`);
        }

        $(".modal").eq(0).css("display", "block");
        $(".modal-wrapper").css("display", "block");
    }
    else
    {
        $(".modal").eq(0).css("display", "none");
        $(".modal-wrapper").css("display", "none");
    }
}

function checkAnswers(id)
{
    const inputs = [$("#when"), $("#where")];
    let isEmpty = false;

    for(let i=0; i<inputs.length; i++)
        if(inputs[i].val() == "")
        {
            $(".warning-text").eq(i).css("display", "inline");
            isEmpty = true;
        }
        else
            $(".warning-text").eq(i).css("display", "none");

    if(!isEmpty)
        apiRequest(1, id);
}

$(window).click(function(event) {
    if(event.target == $(".modal")[0])
        toggleModal(null, null, null);
});