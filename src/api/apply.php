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
            echo json_encode(array("message" => "Field 'job_id' is required."));
            return;
        }

        $jobId = isset($data->job_id) ? trim($data->job_id) : '';
        $question1Answer = isset($data->question1_answer) ? trim($data->question1_answer) : '';
        $question2Answer = isset($data->question2_answer) ? trim($data->question2_answer) : '';
        $question3Answer = isset($data->question3_answer) ? trim($data->question3_answer) : '';

        $candidate = new CandidateController($id);
        $response = $candidate->applyToJob($jobId, $question1Answer, $question2Answer, $question3Answer);

        if($response == 1)
        {
            http_response_code(201);
            echo json_encode(array("message" => "Applied to job."));
        }
        else if($response == 0)
        {
            http_response_code(500);
            echo json_encode(array("message" => "Something went wrong. Try again later."));
        }
        else if($response == -1)
        {
            http_response_code(400);
            echo json_encode(array("message" => "Invalid job ID."));
        }
        else
        {
            http_response_code(422);
            echo json_encode(array("message" => "Already applied to this job."));
        }
    }
}
?>