<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once("../models/database.php");
include_once("../models/company_model.php");
include_once("../controllers/company_controller.php");

if(!isset($_GET["page"]) || !isset($_GET["limit"]))
{
    http_response_code(400);
    echo json_encode(array("message" => "Fields 'page', 'limit' are required."));
    return;
}
else
{
    $companyId = isset($_GET["company_id"]) ? $_GET["company_id"] : 0;
    $keywords = isset($_GET["keywords"]) ? explode(",", $_GET["keywords"]) : '';
    $locationLat = isset($_GET["location_lat"]) ? $_GET["location_lat"] : '';
    $locationLon = isset($_GET["location_lon"]) ? $_GET["location_lon"] : '';
    $skills = isset($_GET["skills"]) ? explode(",", $_GET["skills"]) : '';
    $type = isset($_GET["type"]) ? explode(",", $_GET["type"]) : '';
    $level = isset($_GET["level"]) ? explode(",", $_GET["level"]) : '';
    $salary = isset($_GET["salary"]) ? $_GET["salary"] : '';
    
    $company = new CompanyController($companyId);
    $response = $company->getJobs($_GET["page"], $_GET["limit"], $keywords, $locationLat, $locationLon, $skills, $type, $level, $salary);

    if($response == 0)
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