<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once("../models/database.php");
include_once("../models/candidate_model.php");
include_once("../controllers/candidate_controller.php");

if(!isset($_GET["id"]))
{
    http_response_code(400);
    echo json_encode(array("message" => "User ID is required."));
}
else
{
    $candidate = new CandidateController($_GET["id"], '', '', '', '', '', '', '', '', '');
    $response = $candidate->getExperience();

    if($response == -1)
    {
        http_response_code(404);
        echo json_encode(array("message" => "Candidate not found."));
    }
    else if($response == 0)
    {
        http_response_code(500);
        echo json_encode(array("message" => "Something went wrong. Try again later."));
    }
    else
    {
        http_response_code(200);
        echo json_encode($response);
    }
}
?>