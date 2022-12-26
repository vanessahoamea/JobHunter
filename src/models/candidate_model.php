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
            return $user;
        }

        $stmt = null;
        return -1;
    }

    protected function createExperience($id, $title, $company_id, $company_name, $type, $start_month, $start_year, $end_month, $end_year, $description)
    {
        $params = array($id, $title, $company_name, $type, $start_month, $start_year);
        $params_string = array("id", "title", "company_name", "type", "start_month", "start_year");

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
        if(!empty($end_month))
        {
            array_push($params, $end_month);
            array_push($params_string, "end_month");
        }
        if(!empty($end_year))
        {
            array_push($params, $end_year);
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
            return $experience;
        }

        $stmt = null;
        return -1;
    }
}
?>