<?php
if(!isset($_COOKIE["jwt"]))
{
    header("location: ../login");
    exit();
}
else
{
    require_once("../controllers/jwt_controller.php");

    if(!JWTController::validateToken($_COOKIE["jwt"]))
    {
        header("location: ../");
        exit();
    }

    $data = JWTController::getPayload($_COOKIE["jwt"]);
    $id = $data["id"];
    if($data["account_type"] == "candidate")
        header("location: ../views/candidates.php?id=$id");
    else if($data["account_type"] == "company")
        header("location: ../views/companies.php?id=$id");
    else
        header("location: ../");
}
?>