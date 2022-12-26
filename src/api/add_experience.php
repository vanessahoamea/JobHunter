<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once("../models/database.php");
include_once("../models/candidate_model.php");
include_once("../controllers/candidate_controller.php");
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
            echo json_encode(array("message" => "Fields 'title', 'company_name', 'type', 'start_month', 'start_year' are required."));
            return;
        }

        $title = isset($data->title) ? trim($data->title) : '';
        $company_id = isset($data->company_id) ? trim($data->company_id) : '';
        $company_name = isset($data->company_name) ? trim($data->company_name) : '';
        $type = isset($data->type) ? trim($data->type) : '';
        $start_month = isset($data->start_month) ? trim($data->start_month) : '';
        $start_year = isset($data->start_year) ? trim($data->start_year) : '';
        $end_month = isset($data->end_month) ? trim($data->end_month) : '';
        $end_year = isset($data->end_year) ? trim($data->end_year) : '';
        $description = isset($data->description) ? trim($data->description) : '';

        $candidate = new CandidateController($id, $title, $company_id, $company_name, $type, $start_month, $start_year, $end_month, $end_year, $description);
        $response = $candidate->addExperience();

        if($response == 1)
        {
            http_response_code(201);
            echo json_encode(array("message" => "Added experience to resume."));
        }
        else if($response == 0)
        {
            http_response_code(500);
            echo json_encode(array("message" => "Something went wrong. Try again later."));
        }
        else
        {
            http_response_code(400);
            echo json_encode(array("message" => "Fields 'title', 'company_name', 'type', 'start_month', 'start_year' are required."));
        }
    }
}
?>