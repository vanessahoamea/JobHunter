<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
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

        if($accountType == "candidate")
        {
            $candidate = new CandidateController($id);
            $response = $candidate->getCandidateData();
        }
        else
        {
            $company = new CompanyController($id);
            $response = $company->getCompanyData();
        }

        if($response == -1)
        {
            http_response_code(404);
            echo json_encode(array("message" => "User not found."));
        }
        else if($response == 0)
        {
            http_response_code(500);
            echo json_encode(array("message" => "Something went wrong. Try again later."));
        }
        else
        {
            clearstatcache();

            $fileName = "../assets/images/" . $accountType . "/image_" . $id . ".jpg";
            if(file_exists($fileName))
                $response["profile_picture"] = $fileName;
            else
                $response["profile_picture"] = "../assets/default.jpg";
            
            http_response_code(200);
            echo json_encode($response);
        }
    }
}
?>