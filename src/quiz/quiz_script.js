$(document).ready(function() {
    $(".question").eq(0).css("display", "block");
    
    $(window).click(function(event) {
        if(event.target.classList.contains("option"))
            $(event.target).prev().prop("checked", true);
    });
});

let questionIndex = 0;
let careers = {
    "Architecture and engineering": 0,
    "Arts and culture": 0,
    "Business, sales and management": 0,
    "Computer engineering": 0,
    "Education": 0,
    "Health and medicine": 0,
    "Law": 0
};

function showNext()
{
    //last question leads to results
    if(questionIndex == 4)
    {
        $(".question").eq(questionIndex).css("display", "none");
        $("#results").css("display", "block");
        submit();
    }

    $(".question").eq(questionIndex).css("display", "none");
    $(".question").eq(questionIndex + 1).css("display", "block");
    questionIndex += 1;

    if(questionIndex == 1)
        $(".buttons-container").prepend("<button type='button' class='form-button previous-button' onclick='showPrevious()'>Previous</button>");
}

function showPrevious()
{
    $(".question").eq(questionIndex).css("display", "none");
    $(".question").eq(questionIndex - 1).css("display", "block");
    questionIndex -= 1;

    if(questionIndex == 0)
        $(".buttons-container").find(".previous-button").remove();
}

function submit()
{
    $(".buttons-container").remove();

    const answers = $("input[type='radio']:checked")
    answers.each((index) => {
        answers.eq(index).val().split(",").forEach((number) => {
            const property = Object.keys(careers)[number];
            careers[property]++;
        });
    });

    const result = Object.keys(careers).reduce((a, b) => careers[a] > careers[b] ? a : b);
    let resultText = "";
    if(result == "Architecture and engineering")
        resultText = "People in the architecture and planning fields are responsible for designing new structures or creating aesthetically pleasing, practical and structurally sound environments. Jobs in this field include: architect, civil engineer, landscape architect, sustainable designer, etc.";
    else if(result == "Arts and culture")
        resultText = "This career field is dedicated to enriching people's lives through culture and the sharing of arts and self-expression. Careers in this field are suited to people who love to create and/or have natural talent. Jobs in this field include: musician, animator, video game designer, photographer, etc.";
    else if(result == "Business, sales and management")
        resultText = "These career fields are best for business-minded individuals with a penchant for communication. They work to execute various processes necessary for the functioning of businesses. Jobs in this field include: real estate agent, marketing assistant, accountant, secretary, etc.";
    else if(result == "Computer engineering")
        resultText = "Computer engineering is a diverse career field that generally involves the development of innovative technologies that benefit humanity. Most professions require some degree of mathematics or computer science knowledge. Jobs in this field include: software engineer, web developer, IT consultant, mechanical engineer, etc.";
    else if(result == "Education")
        resultText = "The education field is dedicated to the art of skillfully disseminating knowledge and information to people. It is not just limited to teaching: there are also management, administrative and board member jobs. Jobs in this field include: teacher, school principal, college professor, school librarian, etc.";
    else if(result == "Health and medicine")
        resultText = "This career profession involves healthcare services that provide care for people, which are an essential part of our society. Jobs in this field include: dental assistant, nurse, veterinarian, physical therapist, etc.";
    else if(result == "Law")
        resultText = "Within the law and public policy field, occupations include criminal justice, public policy advocacy and political lobbying. You can find a job working for the government, nonprofits, think tanks and large for-profit companies. Jobs in this field include: lawyer, prosecutor, notary, public administrator, etc.";

    $("#results").append("<h1>" + result + "</h1>");
    $("#results").append("<p>" + resultText + "</p>");
    $("#results").append("<button type='button' class='form-button' onclick='location.reload()'>Retake quiz</button>")
}