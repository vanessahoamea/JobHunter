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
        if(!isset($data->job_id) || !isset($data->candidate_id))
        {
            http_response_code(400);
            echo json_encode(array("message" => "Fields 'job_id', 'candidate_id' are required."));
            return;
        }

        $jobId = trim($data->job_id);
        $candidateId = trim($data->candidate_id);

        $company = new CompanyController($id);
        $response = $company->editApplicantVisibility($jobId, $candidateId);

        if($response == 1)
        {
            http_response_code(201);
            echo json_encode(array("message" => "Modified candidate visibility."));
        }
        else if($response == 0)
        {
            http_response_code(500);
            echo json_encode(array("message" => "Something went wrong. Try again later."));
        }
        else
        {
            http_response_code(404);
            echo json_encode(array("message" => "Job or candidate not found."));
        }
    }
}
?>