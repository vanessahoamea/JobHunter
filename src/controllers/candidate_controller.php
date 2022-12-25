<?php
include_once("../models/candidate_model.php");

class CandidateController extends CandidateModel
{
    private $id;
    private $title;
    private $company_id;
    private $company_name;
    private $type;
    private $startMonth;
    private $startYear;
    private $endMonth;
    private $endYear;
    private $description;

    public function __construct($id, $title, $company_id, $company_name, $type, $startMonth, $startYear, $endMonth, $endYear, $description)
    {
        $this->id = $id;
        $this->title = $title;
        $this->company_id = $company_id;
        $this->company_name = $company_name;
        $this->type = $type;
        $this->startMonth = $startMonth;
        $this->startYear = $startYear;
        $this->endMonth = $endMonth;
        $this->endYear = $endYear;
        $this->description = $description;
    }

    public function addExperience()
    {
        if($this->emptyInput())
            return -1;

        return $this->createExperience($this->id, $this->title, $this->company_id, $this->company_name, $this->type, $this->startMonth, $this->startYear, $this->endMonth, $this->endYear, $this->description);
    }

    private function emptyInput()
    {
        if(empty($this->id) || empty($this->title) || empty($this->company_name) || empty($this->type) || empty($this->startMonth) || empty($this->startYear) || empty($this->endMonth) || empty($this->endYear))
            return true;
        return false;
    }

    public function getExperienceData()
    {
        $experience = $this->getAllExperience($this->id);

        if($experience < 1)
            return $experience;
        
        date_default_timezone_set("Europe/Bucharest");

        $totalMonths = 0;
        $hasDescription = 0;
        for($i=0; $i<count($experience); $i+=1)
        {
            $dateString = $experience[$i]["start_month"] . " 01 " . $experience[$i]["start_year"];
            $startDate = new DateTime($dateString);

            if(isset($experience[$i]["end_month"]) && isset($experience[$i]["end_year"]))
            {
                $dateString = $experience[$i]["end_month"] . " 01 " . $experience[$i]["end_year"];
                $endDate = new DateTime($dateString);
            }
            else
                $endDate = new DateTime(date("l"));

            $interval = $endDate->diff($startDate);
            $totalMonths += ($interval->y) * 12 + $interval->m;

            if(isset($experience[$i]["description"]))
                $hasDescription += 1;
        }

        $result = array(
            "total_months" => $totalMonths,
            "average_employment_period" => round($totalMonths / count($experience), 2),
            "adds_descriptions" => $hasDescription >= count($experience) / 2
        );

        return $result;
    }
}
?>