<?php
include_once("../models/candidate_model.php");

class CandidateController extends CandidateModel
{
    private $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function getCandidateData()
    {
        return $this->getAllData($this->id);
    }

    public function addExperience($title, $companyId, $companyName, $type, $startMonth, $startYear, $endMonth, $endYear, $description)
    {
        if($this->emptyInput(array($title, $companyName, $type, $startMonth, $startYear)))
            return -1;

        return $this->createExperience($this->id, $title, $companyId, $companyName, $type, $startMonth, $startYear, $endMonth, $endYear, $description);
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