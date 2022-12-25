<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once("../models/database.php");
include_once("../models/signup_model.php");
include_once("../controllers/signup_controller.php");

$data = json_decode(file_get_contents("php://input"));
if($data == null)
{
    http_response_code(400);
    echo json_encode(array("message" => "Fields 'first_name', 'last_name', 'email' and 'password' are required."));
    return;
}

$fname = isset($data->first_name) ? trim($data->first_name) : '';
$lname = isset($data->last_name) ? trim($data->last_name) : '';
$email = isset($data->email) ? strtolower(trim($data->email)) : '';
$phone = isset($data->phone) ? trim($data->phone) : '';
$password = isset($data->password) ? $data->password : '';

$params = array($fname, $lname, $email, $phone, $password);
$signup = new SignupController(1, $params);
$signupResponse = $signup->signupCandidate();

if($signupResponse == 1)
{
    http_response_code(201);
    echo json_encode(array("message" => "Successfully signed up."));
}
else if($signupResponse == 0)
{
    http_response_code(500);
    echo json_encode(array("message" => "Something went wrong. Try again later."));
}
else if($signupResponse == -1)
{
    http_response_code(400);
    echo json_encode(array("message" => "Fields 'first_name', 'last_name', 'email' and 'password' are required."));
}
else if($signupResponse == -2)
{
    http_response_code(400);
    echo json_encode(array("message" => "E-mail address is not valid."));
}
else if($signupResponse == -3)
{
    http_response_code(422);
    echo json_encode(array("message" => "An account registered with this e-mail address already exists."));
}
?>