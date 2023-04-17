$(document).ready(function() {
    $.ajax({
        url: "../api/get_notifications.php",
        method: "GET",
        contentType: "application/x-www-form-urlencoded",
        dataType: "json",
        beforeSend: function(xmlhttp) {
            if(getCookie("jwt") != "")
                xmlhttp.setRequestHeader("Authorization", "Bearer " + getCookie("jwt"));
        },
        success: function(response) {
            response = Object.entries(response);

            if(response.length == 0)
            {
                const formattedDate = new Date().toLocaleDateString("en-US", { year: "numeric", month: "long", day: "numeric" });
                $(".daily-notifications").html("<h3>" + formattedDate + "</h3>");
                $(".daily-notifications").append("<p style='text-align: center'><i>Nothing to see here.</i></p>");
                return;
            }

            $(".daily-notifications").remove();
            response.forEach(data => {
                let dailyNotifications = $("<div class='daily-notifications'></div>");
                let card = $("<div class='card'></div>");

                data[1].forEach(notification => {
                    card.append(`<li><a href="../applicants?id=${notification["job_id"]}">${notification["job_title"]}</a> has ${notification["applicants"]} new applicant(s).</li>`);
                });

                const formattedDate = new Date(data[0]).toLocaleDateString("en-US", { year: "numeric", month: "long", day: "numeric" });
                dailyNotifications.append("<h3>" + formattedDate + "</h3>");
                dailyNotifications.append(card);
                $("#wrapper").append(dailyNotifications);
            });
        }
    });
});