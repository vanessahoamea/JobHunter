<?php
include_once("../models/candidate_model.php");

class CandidateController extends CandidateModel
{
    private $id;
    private $title;
    private $companyId;
    private $companyName;
    private $type;
    private $startMonth;
    private $startYear;
    private $endMonth;
    private $endYear;
    private $description;

    public function __construct($id, $title, $companyId, $companyName, $type, $startMonth, $startYear, $endMonth, $endYear, $description)
    {
        $this->id = $id;
        $this->title = $title;
        $this->companyId = $companyId;
        $this->companyName = $companyName;
        $this->type = $type;
        $this->startMonth = $startMonth;
        $this->startYear = $startYear;
        $this->endMonth = $endMonth;
        $this->endYear = $endYear;
        $this->description = $description;
    }

    public function getCandidateData()
    {
        return $this->getAllData($this->id);
    }

    public function addExperience()
    {
        if($this->emptyInput())
            return -1;

        return $this->createExperience($this->id, $this->title, $this->companyId, $this->companyName, $this->type, $this->startMonth, $this->startYear, $this->endMonth, $this->endYear, $this->description);
    }

    private function emptyInput()
    {
        if(empty($this->id) || empty($this->title) || empty($this->companyName) || empty($this->type) || empty($this->startMonth) || empty($this->startYear))
            return true;
        return false;
    }

    public function getExperience()
    {
        $experience = $this->getAllExperience($this->id);
        $months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sept", "Oct", "Nov", "Dec"];

        //sorting the array by most recent job
        usort($experience, function($a, $b) use ($months) {
            $years = $b["start_year"] - $a["start_year"];
            if($years != 0)
                return $years;
            return array_search($b["start_month"], $months) - array_search($a["start_month"], $months);
        });

        return $experience;
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