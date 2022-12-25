<?php
include_once("../models/signup_model.php");

class SignupController extends SignupModel
{
    private $fname;
    private $lname;
    private $cname;
    private $email;
    private $phone;
    private $address;
    private $password;

    public function __construct($accountType, $params)
    {
        if($accountType == 1)
            $this->constructCandidate($params[0], $params[1], $params[2], $params[3], $params[4]);
        else if($accountType == 2)
            $this->constructCompany($params[0], $params[1], $params[2], $params[3]);
        else
            $this->email = $params;
    }

    public function constructCandidate($fname, $lname, $email, $phone, $password)
    {
        $this->fname = $fname;
        $this->lname = $lname;
        $this->email = $email;
        $this->phone = $phone;
        $this->password = $password;
    }

    public function constructCompany($cname, $email, $address, $password)
    {
        $this->cname = $cname;
        $this->email = $email;
        $this->address = $address;
        $this->password = $password;
    }

    public function signupCandidate()
    {
        if($this->emptyInput(1))
            return -1;
        if($this->invalidEmail())
            return -2;
        if($this->userExists("candidates"))
            return -3;

        return $this->createCandidate($this->fname, $this->lname, $this->email, $this->phone, $this->password);
    }

    public function signupCompany()
    {
        if($this->emptyInput(2) == true)
            return -1;
        if($this->invalidEmail() == true)
            return -2;
        if($this->userExists("companies") == true)
            return -3;

        return $this->createCompany($this->cname, $this->email, $this->address, $this->password);
    }

    private function emptyInput($type)
    {
        if($type == 1)
        {
            if(empty($this->fname) || empty($this->lname) || empty($this->email) || empty($this->password))
                return true;
            return false;
        }
        else
        {
            if(empty($this->cname) || empty($this->email) || empty($this->password))
                return true;
            return false;
        }
    }

    private function invalidEmail()
    {
        if(!filter_var($this->email, FILTER_VALIDATE_EMAIL))
            return true;
        return false;
    }

    private function userExists($table)
    {
        if($this->checkUser($this->email, $table) == 1)
            return false;
        return true;
    }
}
?>