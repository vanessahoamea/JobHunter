let skillsArray = [];
function removeSkill(skill)
{
    const index = skillsArray.indexOf($(skill).parent().text());
    if(index > -1)
    {
        skillsArray.splice(index, 1);
        $(skill).parent().remove();
    }
}

$("#skills").on("keyup", function(e) {
    if(e.key == "," || e.key == "Enter")
    {
        let text = e.target.value.toLowerCase().replace(/\s+/g, " ");
        if(text.length > 0 && !skillsArray.includes(text))
            text.split(",").forEach(skill => {
                skill = skill.trim();
                if(skill.length == 0 || skillsArray.includes(skill))
                    return;

                skillsArray.push(skill);
                const listItem = $("<li><i class='fa-solid fa-xmark fa-fw' onclick='removeSkill(this)'></i>" + skill + "</li>");
                $(listItem).insertBefore("#skills");
            });
        
        e.target.value = "";
    }
});