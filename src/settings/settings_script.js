$(document).ready(function () {
    //populate fields with the user's data
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
                type = 0;
                $("#candidate-fname").val(response["first_name"]);
                $("#candidate-lname").val(response["last_name"]);
                $("#candidate-email").val(response["email"]);
                $("#candidate-phone").val(response["phone"]);
                $("#candidate-location").val(response["location"]);
            }
            else
            {
                type = 1;
                $("#company-name").val(response["company_name"]);
                $("#company-email").val(response["email"]);
                $("#company-address").val(response["address"]);
                $("#company-website").val(response["website"]);
            }
        }
    });

    //upload new profile picture
    $("#image-upload").change(function(event) {
        const files = event.target.files;

        if(files && files.length > 0)
        {
            const className = (type == 0) ? "search-button" : "search-button company-button";

            $(".modal-wrapper").empty();
            $(".modal-content").css("width", "70vw");
            if(files[0]["type"].split("/")[0] == "image")
            {
                const reader = new FileReader();
                reader.onload = function() {
                    $(".modal-wrapper").append(`<img id='sample-image' src='${reader.result}' alt='Image preview'></img>`);
                    $(".modal-wrapper").append(`<button class='${className}' style='width: fit-content;' onclick='crop()'>Crop</button>`);
                    
                    cropper = new Cropper($("#sample-image")[0], {
                        aspectRatio: 1,
                        viewMode: 2
                    });
                };
                reader.readAsDataURL(files[0]);
            }
            else
            {
                $(".modal-wrapper").append("<p>The chosen file must be a PNG or JPEG image.</p>");
                $(".modal-wrapper").append(`<button class='${className}' style='width: fit-content;' onclick='toggleModal()'>Close</button>`);
            }

            $(".modal").eq(0).css("display", "block");
            $(".modal-wrapper").css("display", "block");
        }
    });
});

$(window).click(function(event) {
    if(event.target == $(".modal")[0] && $(".modal-wrapper").children("img").length == 0)
        toggleModal();
    
    if(event.target == $("#profile-picture")[0])
        $("#image-upload").click();
});


let type = -1;
let cropper = null;
let changedImage = false;

function toggleModal()
{
    const className = (type == 0) ? "search-button" : "search-button company-button";

    $(".modal-content").css("width", "45vw");
    if($(".modal").eq(0).css("display") == "none")
    {
        $(".modal-wrapper").empty();
        $(".modal-wrapper").append("<p>Enter your password in order to save your changes:</p>");
        $(".modal-wrapper").append("<input type='password' id='verify-password' class='input' style='max-width: 45vw;'>");
        $(".modal-wrapper").append(`<button class='${className}' style='width: fit-content;' onclick='saveChanges()'>Continue</button>`);
        $(".modal").eq(0).css("display", "block");
        $(".modal-wrapper").css("display", "block");
    }
    else
    {
        $(".modal").eq(0).css("display", "none");
        $(".modal-wrapper").css("display", "none");

        if(cropper != null)
        {
            cropper.destroy();
            cropper = null;
        }
    }
}

function saveChanges()
{
    let params = {};
    const currentPassword = $("#verify-password").val();
    const profilePicture = $("#profile-picture").attr("src");

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

    if(changedImage)
        params["profile_picture"] = profilePicture;
    
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
            toggleModal();
            location.reload();
        },
        error: function(xmlhttp) {
            const response = JSON.parse(xmlhttp.responseText);
            const className = (type == 0) ? "search-button" : "search-button company-button";
            const message = (response["message"] == "Field 'current_password' is required.") ? "Password is required." : response["message"];

            $(".modal-wrapper").empty();
            $(".modal-wrapper").append("<p>" + message + "</p>");
            $(".modal-wrapper").append(`<button class='${className}' style='max-width: 45vw;' onclick='toggleModal()'>Close</button>`);
        }
    });
}

function crop()
{
    const canvas = cropper.getCroppedCanvas({
        maxWidth: 300,
        maxHeight: 300
    });

    canvas.toBlob((blob) => {
        url = URL.createObjectURL(blob);
        const reader = new FileReader();
        reader.onloadend = function() {
            $("#profile-picture").attr("src", reader.result);
            toggleModal();
        };
        reader.readAsDataURL(blob);
    });

    changedImage = true;
}

function removePicture()
{
    $("#profile-picture").attr("src", "../assets/default.jpg");
    changedImage = true;
}