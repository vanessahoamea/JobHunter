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
        if(!isset($data->job_id))
        {
            http_response_code(400);
            echo json_encode(array("message" => "Field 'job_id' is required."));
            return;
        }

        $jobId = trim($data->job_id);
        $title = isset($data->title) ? trim($data->title) : '';
        $skills = isset($data->skills) ? json_encode($data->skills) : null;
        $type = isset($data->type) ? trim($data->type) : '';
        $level = isset($data->level) ? trim($data->level) : '';
        $locationName = isset($data->location_name) ? trim($data->location_name) : '';
        $locationCoords = isset($data->location_coords) ? json_encode($data->location_coords) : '';
        $salary = isset($data->salary) ? trim($data->salary) : null;
        $physical = isset($data->physical) ? trim($data->physical) : '';
        $description = isset($data->description) ? urldecode($data->description) : '';
        $question1 = isset($data->question1) ? trim($data->question1) : null;
        $question2 = isset($data->question2) ? trim($data->question2) : null;
        $question3 = isset($data->question3) ? trim($data->question3) : null;

        $company = new CompanyController($id);
        $response = $company->editJob($jobId, $title, $skills, $type, $level, $locationName, $locationCoords, $salary, $physical, $question1, $question2, $question3);

        if($response == 1)
        {
            http_response_code(201);
            echo json_encode(array("message" => "Modified job."));

            //store job description on the server
            if($description != null && $description != "")
            {
                $file = fopen("../assets/jobs/description_" . $jobId . ".html", "w");
                fwrite($file, $description);
                fclose($file);
            }
        }
        else if($response == 0)
        {
            http_response_code(500);
            echo json_encode(array("message" => "Something went wrong. Try again later."));
        }
        else if($response == -1)
        {
            http_response_code(404);
            echo json_encode(array("message" => "Job not found."));
        }
        else
        {
            http_response_code(404);
            echo json_encode(array("message" => "A location name and coordinates must be provided."));
        }
    }
}
?>