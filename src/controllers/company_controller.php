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

    public function getJob($jobId)
    {
        return $this->getSingleJob($jobId);
    }

    public function getJobs($page, $limit, $keywords, $locationLat, $locationLon, $skills, $type, $level, $salary)
    {
        return $this->getRecentJobs($this->id, $page, $limit, $keywords, $locationLat, $locationLon, $skills, $type, $level, $salary);
    }

    public function editJob($jobId, $title, $skills, $type, $level, $locationName, $locationCoords, $salary, $physical)
    {
        return $this->updateJob($this->id, $jobId, $title, $skills, $type, $level, $locationName, $locationCoords, $salary, $physical);
    }

    public function removeJob($jobId)
    {
        return $this->deleteJob($this->id, $jobId);
    }

    public function getJobApplicants($jobId)
    {
        return $this->getApplicants($this->id, $jobId);
    }

    public function validate($jobId)
    {
        return $this->validateJob($this->id, $jobId);
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