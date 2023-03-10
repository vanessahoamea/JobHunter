<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once("../models/database.php");
include_once("../models/company_model.php");
include_once("../controllers/company_controller.php");

if(!isset($_GET["company_id"]) || !isset($_GET["page"]) || !isset($_GET["limit"]))
{
    http_response_code(400);
    echo json_encode(array("message" => "Fields 'company_id', 'page', 'limit' are required."));
}
else
{
    $company = new CompanyController($_GET["company_id"]);
    $response = $company->getCompanyReviews($_GET["page"], $_GET["limit"]);

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