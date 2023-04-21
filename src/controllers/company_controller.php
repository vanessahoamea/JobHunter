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
        $data = $this->getAllRows($this->id, "companies", "id");

        if($data < 1)
            return $data;
        
        $data = $data[0];
        unset($data["password"]);
        return $data;
    }

    public function editCompany($cname, $email, $address, $website, $newPassword, $currentPassword)
    {
        if($this->emptyInput(array($currentPassword)))
            return -1;
        
        return $this->updateCompany($this->id, $cname, $email, $address, $website, $newPassword, $currentPassword);
    }

    public function getNames($companyName)
    {
        return $this->getAllCompanyNames($companyName);
    }

    public function addJob($title, $skills, $type, $level, $locationName, $locationCoords, $physical, $salary, $description, $question1, $question2, $question3, $datePosted)
    {
        if($this->emptyInput(array($title, $type, $level, $locationName, $locationCoords, $physical, $description, $datePosted)))
            return -1;
            
        return $this->createJob($this->id, $title, $skills, $type, $level, $locationName, $locationCoords, $physical, $salary, $question1, $question2, $question3, $datePosted);
    }

    public function getJob($jobId)
    {
        return $this->getSingleJob($jobId);
    }

    public function getJobs($page, $limit, $keywords, $locationLat, $locationLon, $skills, $type, $level, $salary, $candidateId)
    {
        return $this->getRecentJobs($this->id, $page, $limit, $keywords, $locationLat, $locationLon, $skills, $type, $level, $salary, $candidateId);
    }

    public function editJob($jobId, $title, $skills, $type, $level, $locationName, $locationCoords, $salary, $physical, $question1, $question2, $question3)
    {
        return $this->updateJob($this->id, $jobId, $title, $skills, $type, $level, $locationName, $locationCoords, $salary, $physical, $question1, $question2, $question3);
    }

    public function removeJob($jobId)
    {
        return $this->deleteJob($this->id, $jobId);
    }

    public function getJobApplicants($jobId)
    {
        return $this->getApplicants($this->id, $jobId);
    }

    public function getReview($reviewId)
    {
        return $this->getSingleReview($reviewId);
    }

    public function getCompanyReviews($page, $limit)
    {
        $result = $this->getReviews($this->id, $page, $limit, "company_id");

        //reviews are anonymous
        for($i=0; $i<count($result["reviews"]); $i+=1)
            unset($result["reviews"][$i]["candidate_id"]);
        
        return $result;
    }

    public function getNotifications()
    {
        return $this->getApplicantNotifications($this->id);
    }

    public function getNotificationCount()
    {
        $data = $this->getAllRows($this->id, "notifications", "company_id");
        
        if($data < 1)
            return $data;
        
        $total = 0;
        foreach($data as $value)
            $total += $value["unread_notifications"];
        
        return array(
            "company_id" => $this->id,
            "unread_notifications" => $total,
        );
    }

    public function removeNotifications()
    {
        return $this->deleteNotifications($this->id);
    }

    public function validate($itemId, $candidateId, $table)
    {
        if($table == "jobs")
            $data = $this->getAllPairRows(array($this->id, $itemId), $table, array("company_id", "id"));
        else
            $data = $this->getAllPairRows(array($this->id, $candidateId, $itemId), $table, array("company_id", "candidate_id", "id"));
        
        if($data < 1)
            return false;

        return true;
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