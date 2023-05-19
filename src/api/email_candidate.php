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

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

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
        if(!isset($data->job_id) || !isset($data->candidate_id) || !isset($data->when) || !isset($data->where))
        {
            http_response_code(400);
            echo json_encode(array("message" => "Fields 'job_id', 'candidate_id', 'when', 'where' are required."));
            return;
        }

        $jobId = trim($data->job_id);
        $candidateId = trim($data->candidate_id);
        $when = trim($data->when);
        $where = trim($data->where);

        $company = new CompanyController($id);
        $response = $company->getEmailData($jobId, $candidateId);

        if($response == 0)
        {
            http_response_code(500);
            echo json_encode(array("message" => "Something went wrong. Try again later."));
        }
        else if($response == -1)
        {
            http_response_code(404);
            echo json_encode(array("message" => "Job or candidate not found."));
        }
        else
        {
            require("../vendor/autoload.php");
            require("../util/config.php");

            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host = "smtp.gmail.com";
                $mail->SMTPAuth = true;
                $mail->Username = $emailAddress;
                $mail->Password = $emailPassword;
                $mail->SMTPSecure = "tls";
                $mail->Port = 587;

                $mail->setFrom($emailAddress, "JobHunter");
                $mail->addAddress($response["candidate_email"]);
                
                $mail->isHTML(true);
                $mail->Subject = "Interview for {$response['job_title']} @ {$response['company_name']}";
                $mail->Body = "Hello {$response['candidate_name']}!<br/><br/>";
                $mail->Body .= "This is a message from the JobHunter Team. We're letting you know that ";
                $mail->Body .= "<a href='https://localhost/Licenta/views/companies.php?id={$id}'>{$response['company_name']}</a> ";
                $mail->Body .= "have reviewed your application, and they think you are a great fit for the ";
                $mail->Body .= "<a href='https://localhost/Licenta/views/jobs.php?id={$jobId}'>{$response['job_title']}</a> position. ";
                $mail->Body .= "They have scheduled an interview with you. Here are the details:<br/><br/>";
                $mail->Body .= "<b>When</b>:<br/>{$when}<br/><br/>";
                $mail->Body .= "<b>Where</b>:<br/>{$where}<br/><br/>";
                $mail->Body .= "Best of luck!";

                $mail->send();
                $company->confirmEmailSent($jobId, $candidateId);

                http_response_code(200);
                echo json_encode(array("message" => "Message has been sent."));
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(array("message" => "Message could not be sent. {$mail->ErrorInfo}"));
            }
        }
    }
}
?>