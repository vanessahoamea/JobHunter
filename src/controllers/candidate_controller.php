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
        $data = $this->getAllRows($this->id, "candidates", "id");

        if($data < 1)
            return $data;

        $data = $data[0];
        unset($data["password"]);
        return $data;
    }

    public function editCandidate($fname, $lname, $email, $phone, $location, $newPassword, $currentPassword)
    {
        if($this->emptyInput(array($currentPassword)))
            return -1;
        
        return $this->updateCandidate($this->id, $fname, $lname, $email, $phone, $location, $newPassword, $currentPassword);
    }

    public function addAbout($text, $link1, $link2, $link3)
    {
        return $this->createUpdateAbout($this->id, $text, $link1, $link2, $link3);
    }

    public function getAbout()
    {
        $about = $this->getAllRows($this->id, "candidate_about", "candidate_id");

        if($about < 1)
            return $about;

        $data = array(
            "text" => $about[0]["text"],
            "links" => array()
        );
        
        $links = $this->getAllRows($this->id, "candidate_links", "candidate_id");

        if($links == 0)
            return 0;
        if($links == -1)
            return $data;

        for($i=0; $i<count($links); $i+=1)
            if($links[$i]["link"] != null)
                $data["links"]["link" . $links[$i]["link_number"]] = $links[$i]["link"];

        return $data;
    }

    public function addExperience($title, $companyId, $companyName, $type, $startMonth, $startYear, $endMonth, $endYear, $description)
    {
        if($this->emptyInput(array($title, $companyName, $type, $startMonth, $startYear)))
            return -1;
        
        $params = array($title, $this->id, $companyName, $type, $startMonth, $startYear);
        $paramsString = array("title", "candidate_id", "company_name", "type", "start_month", "start_year");

        $optionalParams = array($companyId, $description, $endMonth, $endYear);
        $optionalParamsString = array("company_id", "description", "end_month", "end_year");

        return $this->createItem($params, $paramsString, $optionalParams, $optionalParamsString, "candidate_experience");
    }

    public function getExperience()
    {
        $experience = $this->getAllRows($this->id, "candidate_experience", "candidate_id");

        if($experience < 1)
            return $experience;

        $this->sortByDate($experience);
        return $experience;
    }

    public function getExperienceData()
    {
        $experience = $this->getAllRows($this->id, "candidate_experience", "candidate_id");

        if($experience < 1)
            return $experience;
        
        date_default_timezone_set("Europe/Bucharest");

        $totalMonths = 0;
        $hasDescription = 0;
        $allExperience = array();
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
            
            array_push($allExperience, array(
                "title" => $experience[$i]["title"],
                "company_name" => $experience[$i]["company_name"],
                "company_id" => $experience[$i]["company_id"],
                "work_period" => ($interval->y) * 12 + $interval->m,
            ));
        }

        //extracting 3 longest positions
        usort($allExperience, function($a, $b) {
            return $b["work_period"] - $a["work_period"];
        });
        $allExperience = array_slice($allExperience, 0, 3);

        $result = array(
            "total_months" => $totalMonths,
            "years" => floor($totalMonths / 12),
            "months" => $totalMonths % 12,
            "average_employment_period" => round($totalMonths / count($experience), 2),
            "adds_descriptions" => $hasDescription >= count($experience) / 2,
            "all_experience" => $allExperience,
        );

        return $result;
    }

    public function editExperience($experienceId, $title, $companyId, $companyName, $type, $startMonth, $startYear, $endMonth, $endYear, $ongoing, $description)
    {
        return $this->updateExperience($this->id, $experienceId, $title, $companyId, $companyName, $type, $startMonth, $startYear, $endMonth, $endYear, $ongoing, $description);
    }

    public function removeExperience($experienceId)
    {
        return $this->deleteItem($this->id, $experienceId, "candidate_experience");
    }

    public function addEducation($institutionName, $startMonth, $startYear, $endMonth, $endYear, $degree, $studyField, $description)
    {
        if($this->emptyInput(array($institutionName, $startMonth, $startYear)))
            return -1;

        $params = array($this->id, $institutionName, $startMonth, $startYear,);
        $paramsString = array("candidate_id", "institution_name", "start_month", "start_year");

        $optionalParams = array($endMonth, $endYear, $degree, $studyField, $description);
        $optionalParamsString = array("end_month", "end_year", "degree", "study_field", "description");

        return $this->createItem($params, $paramsString, $optionalParams, $optionalParamsString, "candidate_education");
    }

    public function getEducation()
    {
        $education = $this->getAllRows($this->id, "candidate_education", "candidate_id");

        if($education < 1)
            return $education;

        $this->sortByDate($education);
        return $education;
    }

    public function editEducation($educationId, $institutionName, $startMonth, $startYear, $endMonth, $endYear, $degree, $studyField, $ongoing, $description)
    {
        $params = array($startMonth, $startYear, $endMonth, $endYear, $institutionName, $degree, $studyField, $description);
        $paramsString = array("start_month", "start_year", "end_month", "end_year", "institution_name", "degree", "study_field", "description");

        return $this->updateItem($this->id, $educationId, "candidate_education", $startMonth, $startYear, $endMonth, $endYear, $ongoing, $description, $params, $paramsString);
    }

    public function removeEducation($educationId)
    {
        return $this->deleteItem($this->id, $educationId, "candidate_education");
    }

    public function addProject($projectName, $startMonth, $startYear, $endMonth, $endYear, $projectLink, $description)
    {
        if($this->emptyInput(array($projectName, $startMonth, $startYear)))
            return -1;
        
        $params = array($this->id, $projectName, $startMonth, $startYear,);
        $paramsString = array("candidate_id", "project_name", "start_month", "start_year");

        $optionalParams = array($endMonth, $endYear, $projectLink, $description);
        $optionalParamsString = array("end_month", "end_year", "project_link", "description");

        return $this->createItem($params, $paramsString, $optionalParams, $optionalParamsString, "candidate_projects");
    }

    public function getProjects()
    {
        $projects = $this->getAllRows($this->id, "candidate_projects", "candidate_id");

        if($projects < 1)
            return $projects;

        $this->sortByDate($projects);
        return $projects;
    }

    public function editProject($projectId, $projectName, $startMonth, $startYear, $endMonth, $endYear, $projectLink, $ongoing, $description)
    {
        $params = array($startMonth, $startYear, $endMonth, $endYear, $projectName, $projectLink, $description);
        $paramsString = array("start_month", "start_year", "end_month", "end_year", "project_name", "project_link", "description");

        return $this->updateItem($this->id, $projectId, "candidate_projects", $startMonth, $startYear, $endMonth, $endYear, $ongoing, $description, $params, $paramsString);
    }

    public function removeProject($projectId)
    {
        return $this->deleteItem($this->id, $projectId, "candidate_projects");
    }

    public function applyToJob($jobId, $question1Answer, $question2Answer, $question3Answer)
    {
        return $this->applySaveHide($this->id, $jobId, $question1Answer, $question2Answer, $question3Answer, "applicants");
    }

    public function saveJob($jobId)
    {
        return $this->applySaveHide($this->id, $jobId, null, null, null, "bookmarks");
    }

    public function hideJob($jobId)
    {
        return $this->applySaveHide($this->id, $jobId, null, null, null, "hidden");
    }

    public function getCandidateJobs($type)
    {
        $table = $type == "bookmarked" ? "bookmarks" : ($type == "hidden" ? "hidden" : "applicants");
        return $this->getAppliedSavedHiddenJobs($this->id, $table);
    }

    public function removeCandidateJob($jobId, $type)
    {
        $table = $type == "bookmarked" ? "bookmarks" : ($type == "hidden" ? "hidden" : "applicants");
        return $this->deleteAppliedSavedHiddenJob($this->id, $jobId, $table);
    }

    public function addReview($companyId, $jobTitle, $jobType, $employmentStatus, $pros, $cons, $rating, $datePosted)
    {
        if($this->emptyInput(array($companyId, $jobTitle, $jobType, $employmentStatus, $pros, $cons, $rating)))
            return -1;
        
        return $this->createReview($this->id, $companyId, $jobTitle, $jobType, $employmentStatus, $pros, $cons, $rating, $datePosted);
    }

    public function getCandidateReviews($page, $limit)
    {
        return $this->getReviews($this->id, $page, $limit, "candidate_id");
    }

    public function editReview($reviewId, $jobTitle, $jobType, $employmentStatus, $pros, $cons, $rating)
    {
        return $this->updateReview($this->id, $reviewId, $jobTitle, $jobType, $employmentStatus, $pros, $cons, $rating);
    }

    public function removeReview($reviewId)
    {
        return $this->deleteItem($this->id, $reviewId, "reviews");
    }

    public function canReview($companyId)
    {
        $data = $this->getAllPairRows(array($this->id, $companyId), "candidate_experience", array("candidate_id", "company_id"));

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

    private function sortByDate(&$array)
    {
        $months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

        usort($array, function($a, $b) use ($months) {
            $years = $b["start_year"] - $a["start_year"];
            if($years != 0)
                return $years;
            return array_search($b["start_month"], $months) - array_search($a["start_month"], $months);
        });
    }
}
?>