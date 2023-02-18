<?php
if(isset($_GET["id"]))
{
    header("location: ../views/reviews.php?id=" . $_GET["id"]);
    exit();
}
else
{
    header("location: ../");
    exit();
}
?>