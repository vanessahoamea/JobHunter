<?php
include_once("../controllers/candidate_controller.php");

class CandidateModel extends DBHandler
{
    protected function getAllRows($id, $table, $field)
    {
        $stmt = $this->connect()->prepare("SELECT * FROM " . $table . " WHERE " . $field . " = ?;");

        if(!$stmt->execute(array($id)))
        {
            $stmt = null;
            return 0;
        }

        if($stmt->rowCount() > 0)
        {
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt = null;
            return $data;
        }

        $stmt = null;
        return -1;
    }

    protected function createUpdateAbout($id, $text)
    {
        $alreadyExists = $this->getAllRows($id, "candidate_about", "candidate_id");
        if($alreadyExists == 0)
            return 0;
        else if($alreadyExists == -1)
        {
            $query = "INSERT INTO candidate_about VALUES (?, ?);";
            $params = array($id, $text);
        }
        else
        {
            $query = "UPDATE candidate_about SET text = ? WHERE candidate_id = ?;";
            $params = array($text, $id);
        }

        $stmt = $this->connect()->prepare($query);

        if($text == "")
        {
            $stmt->bindValue(array_search($text, $params) + 1, null, PDO::PARAM_NULL);
            $stmt->bindValue(array_search($id, $params) + 1, $id, PDO::PARAM_INT);

            $params = array();
        }
        
        if(!$stmt->execute($params))
        {
            $stmt = null;
            return 0;
        }

        $stmt = null;
        return 1;
    }

    protected function createItem($params, $paramsString, $optionalParams, $optionalParamsString, $table)
    {
        for($i=0; $i<count($optionalParams); $i+=1)
            if(!empty($optionalParams[$i]))
            {
                array_push($params, $optionalParams[$i]);
                array_push($paramsString, $optionalParamsString[$i]);
            }

        $queryParams = implode(", ", $paramsString);
        $placeholders = implode(", ", array_fill(0, count($params), "?"));
        
        $stmt = $this->connect()->prepare("INSERT INTO " . $table . " (" . $queryParams . ") VALUES (" . $placeholders .");");

        if(!$stmt->execute($params))
        {
            $stmt = null;
            return 0;
        }

        $stmt = null;
        return 1;
    }

    protected function updateExperience($candidateId, $experienceId, $title, $companyId, $companyName, $type, $startMonth, $startYear, $endMonth, $endYear, $ongoing, $description)
    {
        $params = array($startMonth, $startYear, $endMonth, $endYear, $title, $companyId, $companyName, $type, $description);
        $paramsString = array("start_month", "start_year", "end_month", "end_year", "title", "company_id", "company_name", "type", "description");

        //validating user and inputs
        $validateUpdate = $this->validateUpdate($candidateId, $experienceId, "id", "candidate_experience", $startMonth, $startYear, $endMonth, $endYear, $ongoing, $params, $paramsString);
        
        if($validateUpdate < 1)
            return $validateUpdate;

        //validating company
        if(!empty($companyId))
        {
            $data = $this->getAllRows($companyId, "companies", "id");

            if($data == 0)
                return $data;
            if($data == -1 || (!empty($companyName) && $data[0]["company_name"] != $companyName))
                return -3;
            if($data > 0 && empty($companyName))
            {
                array_push($params, $data[0]["company_name"]);
                array_push($paramsString, "company_name");
            }
        }
        else if(!empty($companyName))
        {
            array_push($params, null);
            array_push($paramsString, "company_id");
        }

        //updating the database entry
        return $this->performUpdate($experienceId, "candidate_experience", $ongoing, $description, $params, $paramsString);
    }

    protected function updateItem($candidateId, $itemId, $table, $startMonth, $startYear, $endMonth, $endYear, $ongoing, $description, $params, $paramsString)
    {
        //validating user and inputs
        $validateUpdate = $this->validateUpdate($candidateId, $itemId, "id", $table, $startMonth, $startYear, $endMonth, $endYear, $ongoing, $params, $paramsString);

        if($validateUpdate < 1)
            return $validateUpdate;

        //updating the database entry
        return $this->performUpdate($itemId, $table, $ongoing, $description, $params, $paramsString);
    }

    protected function deleteItem($candidateId, $itemId, $table)
    {
        $data = $this->getAllRows($itemId, $table, "id");

        if($data < 1)
            return $data;

        $stmt = null;
        $stmt = $this->connect()->prepare("DELETE FROM " . $table . " WHERE id = ?;");

        if(!$stmt->execute(array($itemId)))
        {
            $stmt = null;
            return 0;
        }

        $stmt = null;
        return 1;
    }

    protected function applySaveHide($candidateId, $jobId, $table)
    {
        $data = $this->getAllRows($jobId, "jobs", "id");
        if($data < 1)
            return $data;
        
        $data = $this->getAllPairRows(array($candidateId, $jobId), $table, array("candidate_id", "job_id"));
        if($data == 0)
            return $data;
        if(is_array($data) && count($data) > 0)
        {
            $stmt = null;
            return -2;
        }

        $stmt = $this->connect()->prepare("INSERT INTO " . $table . " VALUES (?, ?);");

        if(!$stmt->execute(array($candidateId, $jobId)))
        {
            $stmt = null;
            return 0;
        }

        $stmt = null;
        return 1;
    }

    protected function getAppliedSavedHiddenJobs($id, $table)
    {
        $stmt = $this->connect()->prepare("SELECT job_id FROM " . $table . " WHERE candidate_id = ?;");

        if(!$stmt->execute(array($id)))
        {
            $stmt = null;
            return 0;
        }

        $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = null;
        return $jobs;
    }

    protected function deleteAppliedSavedHiddenJob($candidateId, $jobId, $table)
    {
        $data = $this->getAllPairRows(array($candidateId, $jobId), $table, array("candidate_id", "job_id"));
        
        if($data < 1)
            return $data;

        $stmt = $this->connect()->prepare("DELETE FROM " . $table . " WHERE candidate_id = ? AND job_id = ?;");

        if(!$stmt->execute(array($candidateId, $jobId)))
        {
            $stmt = null;
            return 0;
        }

        $stmt = null;
        return 1;
    }

    private function getAllPairRows($params, $table, $fields)
    {
        $fields = implode(" AND ", array_map(fn($item) => $item . " = ?", $fields));
        $stmt = $this->connect()->prepare("SELECT * FROM " . $table . " WHERE " . $fields . ";");
        
        if(!$stmt->execute($params))
        {
            $stmt = null;
            return 0;
        }

        if($stmt->rowCount() > 0)
        {
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt = null;
            return $data;
        }

        $stmt = null;
        return -1;
    }

    private function validateUpdate($candidateId, $itemId, $itemIdString, $table, $startMonth, $startYear, $endMonth, $endYear, $ongoing, &$params, &$paramsString)
    {
        $data = $this->getAllPairRows(array($candidateId, $itemId), $table, array("candidate_id", $itemIdString));

        if($data < 1)
            return $data;
        else
            $data = $data[0];
        
        //validating dates
        if(is_null($data["end_month"]) && is_null($data["end_year"]) && !$ongoing)
        {
            if((empty($endMonth) && !empty($endYear)) || (!empty($endMonth) && empty($endYear)))
            {
                $stmt = null;
                return -2;
            }
        }

        if(strlen($startMonth) > 3)
            $params[0] = substr($startMonth, 0, 3);
        if(strlen($endMonth) > 3)
            $params[2] = substr($endMonth, 0, 3);

        //handling parameters
        $emptyArray = array_fill(0, count($params), '');
        $params = array_diff($params, $emptyArray);
        $paramsString = array_diff_key($paramsString, array_diff_key($paramsString, $params));

        return 1;
    }

    private function performUpdate($itemId, $table, $ongoing, $description, $params, $paramsString)
    {
        $queryParams = implode(", ", array_map(fn($item) => $item . " = ?", $paramsString));
        array_push($params, $itemId);
        $params = array_values($params);
        
        if(count($params) > 1)
        {
            $stmt = null;
            $stmt = $this->connect()->prepare("UPDATE " . $table . " SET " . $queryParams . " WHERE id = ?;");

            if(!$stmt->execute($params))
            {
                $stmt = null;
                return 0;
            }
        }

        if($ongoing)
        {
            $stmt = null;
            $stmt = $this->connect()->prepare("UPDATE " . $table . " SET end_month = ?, end_year = ? WHERE id = ?;");
            $stmt->bindValue(1, null, PDO::PARAM_NULL);
            $stmt->bindValue(2, null, PDO::PARAM_NULL);
            $stmt->bindValue(3, $itemId, PDO::PARAM_INT);

            if(!$stmt->execute())
            {
                $stmt = null;
                return 0;
            }
        }

        if($description == "")
        {
            $stmt = null;
            $stmt = $this->connect()->prepare("UPDATE " . $table . " SET description = ? WHERE id = ?;");
            $stmt->bindValue(1, null, PDO::PARAM_NULL);
            $stmt->bindValue(2, $itemId, PDO::PARAM_INT);

            if(!$stmt->execute())
            {
                $stmt = null;
                return 0;
            }
        }

        $stmt = null;
        return 1;
    }
}
?>