<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once("../models/database.php");
include_once("../models/candidate_model.php");
include_once("../controllers/candidate_controller.php");
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
        $accountType = $jwt["account_type"];

        $data = json_decode(file_get_contents("php://input"));
        if(!isset($data->current_password))
        {
            http_response_code(400);
            echo json_encode(array("message" => "Field 'current_password' is required."));
            return;
        }

        $currentPassword = isset($data->current_password) ? trim($data->current_password) : '';
        $newPassword = isset($data->new_password) ? trim($data->new_password) : null;
        if($accountType == "candidate")
        {
            $fname = isset($data->first_name) ? trim($data->first_name) : '';
            $lname = isset($data->last_name) ? trim($data->last_name) : '';
            $email = isset($data->email) ? trim($data->email) : '';
            $phone = isset($data->phone) ? trim($data->phone) : null;
            $location = isset($data->location) ? trim($data->location) : null;

            $candidate = new CandidateController($id);
            $response = $candidate->editCandidate($fname, $lname, $email, $phone, $location, $newPassword, $currentPassword);
        }
        else
        {
            $cname = isset($data->company_name) ? trim($data->company_name) : '';
            $email = isset($data->email) ? trim($data->email) : '';
            $address = isset($data->address) ? trim($data->address) : null;
            $website = isset($data->website) ? trim($data->website) : null;

            $company = new CompanyController($id);
            $response = $company->editCompany($cname, $email, $address, $website, $newPassword, $currentPassword);
        }

        if($response == 1)
        {
            http_response_code(201);
            echo json_encode(array("message" => "Saved changes."));
        }
        else if($response == 0)
        {
            http_response_code(500);
            echo json_encode(array("message" => "Something went wrong. Try again later."));
        }
        else if($response == -1)
        {
            http_response_code(400);
            echo json_encode(array("message" => "Field 'current_password' is required."));
        }
        else
        {
            http_response_code(403);
            echo json_encode(array("message" => "Incorrect password."));
        }
    }
}
?>