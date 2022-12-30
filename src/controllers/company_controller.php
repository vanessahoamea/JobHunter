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

    public function addJob($title, $skills, $type, $level, $locationName, $locationCoords, $physical, $salary, $description, $datePosted)
    {
        if($this->emptyInput(array($title, $type, $level, $locationName, $locationCoords, $physical, $description, $datePosted)))
            return -1;
            
        return $this->createJob($this->id, $title, $skills, $type, $level, $locationName, $locationCoords, $physical, $salary, $datePosted);
    }

    public function getJobs($page, $limit)
    {
        return $this->getRecentJobs($this->id, $page, $limit);
    }

    private function emptyInput($params)
    {
        if(empty($this->id))
            return true;
        
        foreach($params as $param)
            if(empty($param))
                return true;

        return false;
    }
}
?>