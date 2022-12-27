<?php
class SignupModel extends DBHandler
{
    protected function checkUser($email, $table)
    {
        $stmt = $this->connect()->prepare("SELECT email FROM " . $table . " WHERE email = ?;");

        if(!$stmt->execute(array($email)))
        {
            $stmt = null;
            return 0;
        }

        if($stmt->rowCount() > 0)
        {
            $stmt = null;
            return -1;
        }

        $stmt = null;
        return 1;
    }

    protected function createCandidate($fname, $lname, $email, $phone, $password)
    {
        $encodedPassword = password_hash($password, PASSWORD_DEFAULT);

        if(empty($phone))
        {
            $stmt = $this->connect()->prepare("INSERT INTO candidates (first_name, last_name, email, password) VALUES (?, ?, ?, ?);");
            $params = array($fname, $lname, $email, $encodedPassword);
        }
        else
        {
            $stmt = $this->connect()->prepare("INSERT INTO candidates (first_name, last_name, email, phone, password) VALUES (?, ?, ?, ?, ?);");
            $params = array($fname, $lname, $email, $phone, $encodedPassword);
        }

        if(!$stmt->execute($params))
        {
            $stmt = null;
            return 0;
        }

        $stmt = null;
        return 1;
    }

    protected function createCompany($cname, $email, $address, $password)
    {
        $encodedPassword = password_hash($password, PASSWORD_DEFAULT);

        if(empty($address))
        {
            $stmt = $this->connect()->prepare("INSERT INTO companies (company_name, email, password) VALUES (?, ?, ?);");
            $params = array($cname, $email, $encodedPassword);
        }
        else
        {
            $stmt = $this->connect()->prepare("INSERT INTO companies (company_name, email, address, password) VALUES (?, ?, ?, ?);");
            $params = array($cname, $email, $address, $encodedPassword);
        }

        if(!$stmt->execute($params))
        {
            $stmt = null;
            return 0;
        }

        $stmt = null;
        return 1;
    }
}
?>