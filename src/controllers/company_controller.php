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

    public function getNames($companyName)
    {
        return $this->getAllCompanyNames($companyName);
    }

    public function getJobs($page, $limit)
    {
        return $this->getRecentJobs($this->id, $page, $limit);
    }
}
?>