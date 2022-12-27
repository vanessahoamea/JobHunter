<?php
include_once("../controllers/candidate_controller.php");

class CandidateModel extends DBHandler
{
    protected function getAllData($id)
    {
        $stmt = $this->connect()->prepare("SELECT first_name, last_name, email, phone FROM candidates WHERE id = ?;");

        if(!$stmt->execute(array($id)))
        {
            $stmt = null;
            return 0;
        }

        if($stmt->rowCount() > 0)
        {
            $user = $stmt->fetchAll(PDO::FETCH_ASSOC)[0];
            $stmt = null;
            return $user;
        }

        $stmt = null;
        return -1;
    }

    protected function createExperience($id, $title, $companyId, $companyName, $type, $startMonth, $startYear, $endMonth, $endYear, $description)
    {
        $params = array($id, $title, $companyName, $type, $startMonth, $startYear);
        $params_string = array("id", "title", "company_name", "type", "start_month", "start_year");

        if(!empty($companyId))
        {
            array_push($params, $companyId);
            array_push($params_string, "company_id");
        }
        if(!empty($description))
        {
            array_push($params, $description);
            array_push($params_string, "description");
        }
        if(!empty($endMonth))
        {
            array_push($params, $endMonth);
            array_push($params_string, "end_month");
        }
        if(!empty($endYear))
        {
            array_push($params, $endYear);
            array_push($params_string, "end_year");
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
            $experience = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt = null;
            return $experience;
        }

        $stmt = null;
        return -1;
    }
}
?>