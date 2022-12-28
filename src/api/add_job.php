<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once("../models/database.php");
include_once("../models/company_model.php");
include_once("../controllers/company_controller.php");
include_once("../controllers/jwt_controller.php");

$headers = apache_request_headers();
if(!isset($headers["Authorization"]))
{
    http_response_code(400);
    echo json_encode(array("message" => "Bearer token is required."));
}
else
{
    $token = explode(" ", trim($headers["Authorization"]))[1];
    if(JWTController::validateToken($token) == false)
    {
        http_response_code(401);
        echo json_encode(array("message" => "You don't have access to this resource."));
    }
    else
    {
        $jwt = JWTController::getPayload($token);
        $id = $jwt["id"];

        $data = json_decode(file_get_contents("php://input"));
        if($data == null)
        {
            http_response_code(400);
            echo json_encode(array("message" => "Fields 'title', 'skills', 'type', 'level', 'location_id', 'location_name', 'physical', 'date_posted' are required."));
            return;
        }

        $title = isset($data->title) ? trim($data->title) : '';
        $skills = isset($data->skills) ? trim($data->skills) : '';
        $type = isset($data->type) ? trim($data->type) : '';
        $level = isset($data->level) ? trim($data->level) : '';
        $locationId = isset($data->location_id) ? trim($data->location_id) : '';
        $locationName = isset($data->location_name) ? trim($data->location_name) : '';
        $physical = isset($data->physical) ? trim($data->physical) : '';
        $salary = isset($data->salary) ? trim($data->salary) : '';
        $datePosted = isset($data->date_posted) ? trim($data->date_posted) : '';

        $candidate = new CompanyController($id);
        $response = $candidate->addJob($title, $skills, $type, $level, $locationId, $locationName, $physical, $salary, $datePosted);

        if($response == 1)
        {
            http_response_code(201);
            echo json_encode(array("message" => "Posted job."));
        }
        else if($response == 0)
        {
            http_response_code(500);
            echo json_encode(array("message" => "Something went wrong. Try again later."));
        }
        else
        {
            http_response_code(400);
            echo json_encode(array("message" => "Fields 'title', 'skills', 'type', 'level', 'location_id', 'location_name', 'physical', 'date_posted' are required."));
        }
    }
}
?>