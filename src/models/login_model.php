<?php
include_once("../controllers/jwt_controller.php");

class LoginModel extends DBHandler
{
    protected function getUser($email, $password)
    {
        $stmt = $this->connect()->prepare("SELECT * FROM candidates WHERE email = ?;");

        if(!$stmt->execute(array($email)))
        {
            $stmt = null;
            return 0;
        }

        if($stmt->rowCount() > 0)
        {
            $user = $stmt->fetchAll(PDO::FETCH_ASSOC)[0];
            $checkPassword = password_verify($password, $user["password"]);

            if(!$checkPassword)
            {
                $stmt = null;
                return -3;
            }

            $stmt = null;
            
            $params = array(
                "id" => $user["id"],
                "first_name" => $user["first_name"],
                "last_name" => $user["last_name"],
                "email" => $user["email"],
                "phone" => $user["phone"],
                "account_type" => "candidate"
            );
            $jwt = new JWTController($params);
            $jwtToken = $jwt->generateToken();

            return $jwtToken;
        }
        else
        {
            $stmt = $this->connect()->prepare("SELECT * FROM companies WHERE email = ?;");

            if(!$stmt->execute(array($email)))
            {
                $stmt = null;
                return 0;
            }

            if($stmt->rowCount() == 0)
            {
                $stmt = null;
                return -2;
            }

            $user = $stmt->fetchAll(PDO::FETCH_ASSOC)[0];
            $checkPassword = password_verify($password, $user["password"]);

            if(!$checkPassword)
            {
                $stmt = null;
                return -3;
            }

            $stmt = null;
            
            $params = array(
                "id" => $user["id"],
                "company_name" => $user["company_name"],
                "email" => $user["email"],
                "address" => $user["address"],
                "account_type" => "company"
            );
            $jwt = new JWTController($params);
            $jwtToken = $jwt->generateToken();

            return $jwtToken;
        }
    }
}
?>