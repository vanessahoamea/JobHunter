<?php
include_once("../models/company_model.php");

class CompanyController extends CompanyModel
{
    private $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function getCompanyData()
    {
        return $this->getAllData($this->id);
    }

    public function getNames($company_name)
    {
        return $this->getAllCompanyNames($company_name);
    }
}
?>