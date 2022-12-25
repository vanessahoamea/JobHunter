<?php
include_once("../models/login_model.php");

class LoginController extends LoginModel
{
    private $email;
    private $password;

    public function __construct($email, $password)
    {
        $this->email = $email;
        $this->password = $password;
    }

    public function loginUser()
    {
        if($this->emptyInput())
            return -1;
        
        return $this->getUser($this->email, $this->password);
    }
    
    private function emptyInput()
    {
        if(empty($this->email) || empty($this->password))
            return true;
        return false;
    }
}
?>