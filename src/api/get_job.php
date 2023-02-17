<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once("../models/database.php");
include_once("../models/company_model.php");
include_once("../controllers/company_controller.php");

if(!isset($_GET["id"]))
{
    http_response_code(400);
    echo json_encode(array("message" => "Job ID is required."));
}
else
{
    $company = new CompanyController(0);
    $response = $company->getJob($_GET["id"]);

    if($response == -1)
    {
        http_response_code(404);
        echo json_encode(array("message" => "Job not found."));
    }
    else if($response == 0)
    {
        http_response_code(500);
        echo json_encode(array("message" => "Something went wrong. Try again later."));
    }
    else
    {
        clearstatcache();

        $fileName = "../assets/jobs/description_" . $_GET["id"] . ".html";
        $file = fopen($fileName, "r");
        $response["description"] = fread($file, filesize($fileName));
        fclose($file);

        $fileName = "../assets/images/company/image_" . $response["company_id"] . ".jpg";
        if(file_exists($fileName))
            $response["profile_picture"] = $fileName;
        else
            $response["profile_picture"] = "../assets/default.jpg";

        http_response_code(200);
        echo json_encode($response);
    }
}
?>