<?php
require_once("../controllers/jwt_controller.php");

if(!isset($_COOKIE["jwt"]))
{
    header("location: ../login");
    exit();
}
else
{
    $data = JWTController::getPayload($_COOKIE["jwt"]);
    if($data["account_type"] == "candidate")
        include("../views/candidates.php");
    else
        include("../views/companies.php?");
}
?>