<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once("../models/database.php");
include_once("../models/company_model.php");
include_once("../controllers/company_controller.php");

if(!isset($_GET["company_name"]))
{
    http_response_code(400);
    echo json_encode(array("message" => "Field 'company_name' is required."));
    return;
}
else
{
    $companyName = strtolower(trim($_GET["company_name"]));

    $company = new CompanyController(0);
    $response = $company->getNames($companyName);

    if($response == 0)
    {
        http_response_code(500);
        echo json_encode(array("message" => "Something went wrong. Try again later."));
    }
    else
    {
        http_response_code(200);
        echo json_encode(array("data" => $response));
    }
}
?>