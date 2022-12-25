<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once("../models/database.php");
include_once("../models/login_model.php");
include_once("../controllers/login_controller.php");

$data = json_decode(file_get_contents("php://input"));
if($data == null)
{
    http_response_code(400);
    echo json_encode(array("message" => "Fields 'email' and 'password' are required."));
    return;
}

$email = $data->email;
$password = $data->password;

$login = new LoginController($email, $password);
$loginResponse = $login->loginUser();

if($loginResponse == 0)
{
    http_response_code(500);
    echo json_encode(array("message" => "Something went wrong. Try again later."));
}
else if($loginResponse == -1)
{
    http_response_code(400);
    echo json_encode(array("message" => "Fields 'email' and 'password' are required."));
}
else if($loginResponse == -2)
{
    http_response_code(404);
    echo json_encode(array("message" => "User does not exist."));
}
else if($loginResponse == -3)
{
    http_response_code(403);
    echo json_encode(array("message" => "Incorrect password."));
}
else
{
    http_response_code(200);
    echo json_encode(array(
        "message" => "Signed in successfully.",
        "jwt" => $loginResponse
    ));
}
?>