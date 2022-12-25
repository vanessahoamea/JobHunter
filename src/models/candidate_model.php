<?php
include_once("../controllers/candidate_controller.php");

class CandidateModel extends DBHandler
{
    protected function createExperience($id, $title, $company_id, $company_name, $type, $startMonth, $startYear, $endMonth, $endYear, $description)
    {
        $params = array($id, $title, $company_name, $type, $startMonth, $startYear, $endMonth, $endYear);
        $params_string = array("id", "title", "company_name", "type", "start_month", "start_year", "end_month", "end_year");

        if(!empty($company_id))
        {
            array_push($params, $company_id);
            array_push($params_string, "company_id");
        }
        if(!empty($description))
        {
            array_push($params, $description);
            array_push($params_string, "description");
        }

        $query_params = implode(", ", $params_string);
        $placeholders = implode(", ", array_fill(0, count($params), "?"));
        
        $stmt = $this->connect()->prepare("INSERT INTO candidate_experience (" . $query_params . ") VALUES (" . $placeholders .");");

        if(!$stmt->execute($params))
        {
            $stmt = null;
            return 0;
        }

        $stmt = null;
        return 1;
    }

    protected function getAllExperience($id)
    {
        $stmt = $this->connect()->prepare("SELECT * FROM candidate_experience WHERE id = ?;");

        if(!$stmt->execute(array($id)))
        {
            $stmt = null;
            return 0;
        }

        if($stmt->rowCount() > 0)
        {
            $exp = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $exp;
        }

        $stmt = null;
        return -1;
    }
}
?>