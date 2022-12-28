let headerText = $("#choose-text");
let boxes = $("#signup-boxes");

let formContainer = $("#signup-form");
let candidateForm = $("#candidate-form");
let companyForm = $("#company-form");
let returnButton = $("#return-button");
let submitButton = $("#submit-button");

let resultText = $("#result-text");

let currentType = -1;

function showForm(type)
{
    formContainer.css("display", "block");
    headerText.css("display", "none");
    boxes.css("display", "none");
    returnButton.css("display", "inline");
    currentType = type;

    if(type == 0)
    {
        candidateForm.css("display", "flex");
        companyForm.css("display", "none");
        submitButton.removeClass("company-button");
    }
    else
    {
        candidateForm.css("display", "none");
        companyForm.css("display", "flex");
        submitButton.addClass("company-button");
    }
}

function showBoxes()
{
    headerText.css("display", "block");
    boxes.css("display", "flex");

    formContainer.css("display", "none");
    returnButton.css("display", "none");

    resultText.css("display", "none");

    candidateForm[0].reset();
    companyForm[0].reset();
    $(".warning-text").remove();
}

function checkValues()
{    
    let params = null;
    let notNullValues = [];
    let endpoint = currentType == 0 ? "candidate_signup.php" : "company_signup.php";

    if(currentType == 0)
    {
        const fname = $("#fname").val().trim();
        const lname = $("#lname").val().trim();
        const email = $("#email").val().trim().toLowerCase();
        const phone = $("#phone").val().trim();
        const password = $("#password").val();

        params = {
            "first_name": fname,
            "last_name": lname,
            "email": email,
            "phone": phone,
            "password": password
        };
        notNullValues = [$("#fname"), $("#lname"), $("#email"), $("#password")];
    }
    else
    {
        const cname = $("#cname").val().trim();
        const cemail = $("#cemail").val().trim().toLowerCase();
        const address = $("#address").val().trim();
        const cpassword = $("#cpassword").val();

        params = {
            "company_name": cname,
            "email": cemail,
            "address": address,
            "password": cpassword
        };
        notNullValues = [$("#cname"), $("#cemail"), $("#cpassword")];
    }

    for(let i=0; i<notNullValues.length; i++)
    {
        notNullValues[i].prev().children().remove(".warning-text");
        if(notNullValues[i].val() == "")
            notNullValues[i].prev().append("<span class='warning-text'>(this field is required)</span>");
    }
    
    if(!Object.values(params).splice(params.length-2, 1).includes(""))
    {
        $.ajax({
            url: "../api/" + endpoint,
            method: "POST",
            data: JSON.stringify(params),
            contentType: "application/x-www-form-urlencoded",
            dataType: "json",
            success: function() {
                resultText.css("color", "green");
            },
            error: function() {
                resultText.css("color", "red");
            },
            complete: function(xmlhttp) {
                response = JSON.parse(xmlhttp.responseText);
                
                if(!response["message"].startsWith("Fields"))
                {
                    resultText.text(response["message"]);
                    resultText.css("display", "block");
                    returnButton[0].scrollIntoView({behavior: "smooth"});
                }
            }
        });
    }
}