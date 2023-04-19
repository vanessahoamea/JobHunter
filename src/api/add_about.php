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
        $text = isset($data->text) ? trim($data->text) : '';
        $link1 = isset($data->link1) ? trim($data->link1) : '';
        $link2 = isset($data->link2) ? trim($data->link2) : '';
        $link3 = isset($data->link3) ? trim($data->link3) : '';

        $candidate = new CandidateController($id);
        $response = $candidate->addAbout($text, $link1, $link2, $link3);

        if($response == 1)
        {
            http_response_code(201);
            echo json_encode(array("message" => "Updated about."));
        }
        else
        {
            http_response_code(500);
            echo json_encode(array("message" => "Something went wrong. Try again later."));
        }
    }
}
?>