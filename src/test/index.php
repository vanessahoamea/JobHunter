<?php
if(file_exists("../assets/jobs/description_1.html"))
{
    $file = fopen("../assets/jobs/description_1.html", "r");
    $ok = fread($file, filesize("../assets/jobs/description_1.html"));
    fclose($file);
    echo $ok;
}
else
    echo "no";
?>
