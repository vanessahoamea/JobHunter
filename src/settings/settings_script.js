$(document).ready(function () {
    $.ajax({
        url: "../api/get_user.php",
        method: "GET",
        contentType: "application/x-www-form-urlencoded",
        dataType: "json",
        beforeSend: function(xmlhttp) {
            xmlhttp.setRequestHeader("Authorization", "Bearer " + getCookie("jwt"));
        },
        success: function(response) {
            $("#profile-picture").attr("src", response["profile_picture"]);

            if($("#candidate-form").length > 0)
            {
                $("#candidate-fname").val(response["first_name"]);
                $("#candidate-lname").val(response["last_name"]);
                $("#candidate-email").val(response["email"]);
                $("#candidate-phone").val(response["phone"]);
                $("#candidate-location").val(response["location"]);
            }
            else
            {
                $("#company-name").val(response["company_name"]);
                $("#company-email").val(response["email"]);
                $("#company-address").val(response["address"]);
                $("#company-website").val(response["website"]);
            }
        }
    });
});

$(window).click(function(event) {
    if(event.target == $(".modal")[0])
        toggleModal(null);
});

function toggleModal(type)
{
    const className = (type == 0) ? "search-button" : "search-button company-button";

    if($(".modal").eq(0).css("display") == "none")
    {
        $(".modal-wrapper").empty();
        $(".modal-wrapper").append("<p>Enter your password in order to save your changes:</p>");
        $(".modal-wrapper").append("<input type='password' id='verify-password' class='input' style='max-width: 45vw;'>");
        $(".modal-wrapper").append(`<button class='${className}' style='max-width: 45vw;' onclick='saveChanges(${type})'>Continue</button>`);
        $(".modal").eq(0).css("display", "block");
        $(".modal-wrapper").css("display", "block");
    }
    else
    {
        $(".modal").eq(0).css("display", "none");
        $(".modal-wrapper").css("display", "none");
    }
}

function saveChanges(type)
{
    let params = {};
    const currentPassword = $("#verify-password").val();

    if(type == 0)
    {
        const firstName = $("#candidate-fname").val();
        const lastName = $("#candidate-lname").val();
        const email = $("#candidate-email").val();
        const phone = $("#candidate-phone").val();
        const location = $("#candidate-location").val();
        const newPassword = $("#candidate-password").val();

        params = {
            "first_name": firstName,
            "last_name": lastName,
            "email": email,
            "phone": phone,
            "location": location,
            "new_password": newPassword,
        };
    }
    else
    {
        const companyName = $("#company-name").val();
        const email = $("#company-email").val();
        const address = $("#company-address").val();
        const website = $("#company-website").val();
        const newPassword = $("#company-password").val();

        params = {
            "company_name": companyName,
            "email": email,
            "address": address,
            "website": website,
            "new_password": newPassword,
        };
    }

    $.ajax({
        url: "../api/edit_user.php",
        method: "POST",
        data: JSON.stringify({...params, "current_password": currentPassword}),
        contentType: "application/x-www-form-urlencoded",
        dataType: "json",
        beforeSend: function(xmlhttp) {
            xmlhttp.setRequestHeader("Authorization", "Bearer " + getCookie("jwt"));
        },
        success: function() {
            toggleModal(null);
            location.reload();
        },
        error: function(xmlhttp) {
            const response = JSON.parse(xmlhttp.responseText);
            const className = (type == 0) ? "search-button" : "search-button company-button";
            const message = (response["message"] == "Field 'current_password' is required.") ? "Password is required." : response["message"];

            $(".modal-wrapper").empty();
            $(".modal-wrapper").append("<p>" + message + "</p>");
            $(".modal-wrapper").append(`<button class='${className}' style='max-width: 45vw;' onclick='toggleModal(null)'>Close</button>`);
        }
    });
}