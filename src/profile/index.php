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
    $id = $data["id"];
    if($data["account_type"] == "candidate")
        header("location: ../views/candidates.php?id=$id");
    else
        header("location: ../views/companies.php?id=$id");
}
?>