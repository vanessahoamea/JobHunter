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
            echo json_encode(array("message" => "Fields 'company_id', 'job_title', 'job_type', 'employment_status', 'pros', 'cons', 'rating' are required."));
            return;
        }

        $companyId = isset($data->company_id) ? trim($data->company_id) : '';
        $jobTitle = isset($data->job_title) ? trim($data->job_title) : '';
        $jobType = isset($data->job_type) ? trim($data->job_type) : '';
        $employmentStatus = isset($data->employment_status) ? trim($data->employment_status) : '';
        $pros = isset($data->pros) ? trim($data->pros) : '';
        $cons = isset($data->cons) ? trim($data->cons) : '';
        $rating = isset($data->rating) ? trim($data->rating) : '';
        $datePosted = date("Y-m-d");

        $candidate = new CandidateController($id);
        $response = $candidate->addRating($companyId, $jobTitle, $jobType, $employmentStatus, $pros, $cons, $rating, $datePosted);

        if($response == 1)
        {
            http_response_code(201);
            echo json_encode(array("message" => "Posted review."));
        }
        else if($response == 0)
        {
            http_response_code(500);
            echo json_encode(array("message" => "Something went wrong. Try again later."));
        }
        else
        {
            http_response_code(400);
            echo json_encode(array("message" => "Fields 'company_id', 'job_title', 'job_type', 'employment_status', 'pros', 'cons', 'rating' are required."));
        }
    }
}
?>