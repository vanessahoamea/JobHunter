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
            echo json_encode(array("message" => "Fields 'title', 'type', 'level', 'location_name', 'location_coords', 'physical', 'description', 'date_posted' are required."));
            return;
        }

        $title = isset($data->title) ? trim($data->title) : '';
        $skills = isset($data->skills) ? json_encode($data->skills) : '';
        $type = isset($data->type) ? trim($data->type) : '';
        $level = isset($data->level) ? trim($data->level) : '';
        $locationName = isset($data->location_name) ? trim($data->location_name) : '';
        $locationCoords = isset($data->location_coords) ? json_encode($data->location_coords) : '';
        $physical = isset($data->physical) ? trim($data->physical) : '';
        $salary = isset($data->salary) ? trim($data->salary) : '';
        $description = isset($data->description) ? urldecode($data->description) : '';
        $datePosted = date("Y-m-d");

        $company = new CompanyController($id);
        $response = $company->addJob($title, $skills, $type, $level, $locationName, $locationCoords, $physical, $salary, $description, $datePosted);

        if($response == -1)
        {
            http_response_code(400);
            echo json_encode(array("message" => "Fields 'title', 'type', 'level', 'location_name', 'location_coords', 'physical', 'description', 'date_posted' are required."));
        }
        else if($response == 0)
        {
            http_response_code(500);
            echo json_encode(array("message" => "Something went wrong. Try again later."));
        }
        else
        {
            http_response_code(201);
            echo json_encode(array(
                "message" => "Posted job.",
                "id" => $response
            ));

            //store job description on the server
            $file = fopen("../assets/jobs/description_" . $response . ".html", "w");
            fwrite($file, $description);
            fclose($file);
        }
    }
}
?>