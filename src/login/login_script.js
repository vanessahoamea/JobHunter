function checkValues()
{
    let errorText = $("#error-text");
    let email = $("#email").val().trim().toLowerCase();
    let password = $("#password").val();

    const fields = [$("#email"), $("#password")];

    for(let i=0; i<fields.length; i++)
    {
        fields[i].prev().children().remove(".warning-text");
        if(fields[i].val() == "")
            fields[i].prev().append("<span class='warning-text'>(this field is required)</span>");
    }
    
    if(email != "" && password != "")
    {
        let params = {
            "email": email,
            "password": password
        }

        $.ajax({
            url: "../api/login.php",
            method: "POST",
            data: JSON.stringify(params),
            contentType: "application/x-www-form-urlencoded",
            dataType: "json",
            success: function(response) {
                errorText.css("display", "none");

                const token = response["jwt"];
                const date = new Date();
                date.setTime(date.getTime() + (30 * 24 * 60 * 60 * 1000));
                const expires = "expires=" + date.toUTCString();

                document.cookie = "jwt=" + token + "; " + expires + "; path=/";
                window.location.href = "../profile";
            },
            error: function(xmlhttp) {
                response = JSON.parse(xmlhttp.responseText);
                
                errorText.text(response["message"]);
                errorText.css("display", "block");
            }
        });
    }
}