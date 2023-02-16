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
        if(!isset($data->project_id))
        {
            http_response_code(400);
            echo json_encode(array("message" => "Field 'project_id' is required."));
            return;
        }

        $projectId = trim($data->project_id);
        $projectName = isset($data->project_name) ? trim($data->project_name) : '';
        $startMonth = isset($data->start_month) ? trim($data->start_month) : '';
        $startYear = isset($data->start_year) ? trim($data->start_year) : '';
        $endMonth = isset($data->end_month) ? trim($data->end_month) : '';
        $endYear = isset($data->end_year) ? trim($data->end_year) : '';
        $projectLink = isset($data->project_link) ? trim($data->project_link) : '';
        $ongoing = isset($data->ongoing) ? trim($data->ongoing) : false;
        $description = isset($data->description) ? trim($data->description) : null;

        $candidate = new CandidateController($id);
        $response = $candidate->editProject($projectId, $projectName, $startMonth, $startYear, $endMonth, $endYear, $projectLink, $ongoing, $description);

        if($response == 1)
        {
            http_response_code(201);
            echo json_encode(array("message" => "Modified project."));
        }
        else if($response == 0)
        {
            http_response_code(500);
            echo json_encode(array("message" => "Something went wrong. Try again later."));
        }
        else if($response == -1)
        {
            http_response_code(404);
            echo json_encode(array("message" => "Project not found."));
        }
        else
        {
            http_response_code(404);
            echo json_encode(array("message" => "A full end date must be provided."));
        }
    }
}
?>