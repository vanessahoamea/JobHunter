<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once("../models/database.php");
include_once("../models/company_model.php");
include_once("../controllers/company_controller.php");

function filter($object, $rating)
{
    return $object["rating"] == $rating;
}

if(!isset($_GET["company_id"]))
{
    http_response_code(400);
    echo json_encode(array("message" => "Fields 'company_id', 'page', 'limit' are required."));
}
else
{
    $company = new CompanyController($_GET["company_id"]);
    $response = $company->getCompanyReviews(1, 9999);

    if($response == 0)
    {
        http_response_code(500);
        echo json_encode(array("message" => "Something went wrong. Try again later."));
    }
    else
    {
        $five = count(array_filter($response["reviews"], fn($object) => filter($object, 5)));
        $four = count(array_filter($response["reviews"], fn($object) => filter($object, 4)));
        $three = count(array_filter($response["reviews"], fn($object) => filter($object, 3)));
        $two = count(array_filter($response["reviews"], fn($object) => filter($object, 2)));
        $one = count(array_filter($response["reviews"], fn($object) => filter($object, 1)));

        $rating = (5 * $five + 4 * $four + 3 * $three + 2 * $two + $one) / $response["total_count"];
        $rating = round($rating);

        http_response_code(200);
        echo json_encode(array(
            "total_count" => $response["total_count"],
            "5" => $five,
            "4" => $four,
            "3" => $three,
            "2" => $two,
            "1" => $one,
            "rating" => $rating
        ));
    }
}
?>