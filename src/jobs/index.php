<?php
if(isset($_GET["id"]))
{
    header("location: ../views/jobs.php?id=" . $_GET["id"]);
    exit();
}
else
{
    header("location: ../");
    exit();
}
?>