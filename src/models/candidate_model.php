<?php
include_once("../controllers/candidate_controller.php");

class CandidateModel extends DBHandler
{
    protected function updateCandidate($id, $fname, $lname, $email, $phone, $location, $newPassword, $currentPassword)
    {
        $candidate = $this->getAllRows($id, "candidates", "id");

        if($candidate == 0)
            return $candidate;
        
        $candidate = $candidate[0];
        if(!password_verify($currentPassword, $candidate["password"]))
            return -2;
        
        $encodedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $params = array($fname, $lname, $email);
        $paramsString = array("first_name", "last_name", "email");
        if($newPassword != "")
        {
            array_push($params, $encodedPassword);
            array_push($paramsString, "password");
        }

        //removing optional values
        $emptyArray = array_fill(0, count($params), '');
        $params = array_diff($params, $emptyArray);
        $paramsString = array_diff_key($paramsString, array_diff_key($paramsString, $params));

        $params = array_values($params);
        array_push($params, $phone);
        array_push($params, $location);
        array_push($params, $id);
        array_push($paramsString, "phone");
        array_push($paramsString, "location");

        //modifying database entry
        $queryParams = implode(", ", array_map(fn($item) => $item . " = ?", $paramsString));
        $stmt = $this->connect()->prepare("UPDATE candidates SET " . $queryParams . " WHERE id = ?;");

        if(!$stmt->execute($params))
        {
            $stmt = null;
            return 0;
        }

        if($phone == "")
        {
            $stmt = null;
            $response = $this->setNullValue("candidates", "phone", $id);

            if($response < 1)
                return $response;
        }

        if($location == "")
        {
            $stmt = null;
            $response = $this->setNullValue("candidates", "location", $id);

            if($response < 1)
                return $response;
        }

        $stmt = null;
        return 1;
    }

    protected function createUpdateAbout($id, $text)
    {
        $params = array($text, $id);
        $alreadyExists = $this->getAllRows($id, "candidate_about", "candidate_id");

        if($alreadyExists == 0)
            return 0;
        else if($alreadyExists == -1)
            $query = "INSERT INTO candidate_about (text, candidate_id) VALUES (?, ?);";
        else
            $query = "UPDATE candidate_about SET text = ? WHERE candidate_id = ?;";
        
        $stmt = $this->connect()->prepare($query);

        if($text == "")
        {
            $stmt->bindValue(1, null, PDO::PARAM_NULL);
            $stmt->bindValue(2, $id, PDO::PARAM_INT);

            if(!$stmt->execute())
            {
                $stmt = null;
                return 0;
            }
        }
        else
        {
            if(!$stmt->execute($params))
            {
                $stmt = null;
                return 0;
            }
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
        $data = $this->getAllPairRows(array($candidateId, $itemId), $table, array("candidate_id", "id"));

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

    protected function createReview($candidateId, $companyId, $jobTitle, $jobType, $employmentStatus, $pros, $cons, $rating, $datePosted)
    {
        $queryParams = implode(", ", array("candidate_id", "company_id", "job_title", "job_type", "employment_status", "pros", "cons", "rating", "date_posted"));
        $stmt = $this->connect()->prepare("INSERT INTO reviews (" . $queryParams . ") VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);");

        if(!$stmt->execute(array($candidateId, $companyId, $jobTitle, $jobType, $employmentStatus, $pros, $cons, $rating, $datePosted)))
        {
            $stmt = null;
            return 0;
        }

        $stmt = null;
        return 1;
    }

    protected function updateReview($candidateId, $reviewId, $jobTitle, $jobType, $employmentStatus, $pros, $cons, $rating)
    {
        $data = $this->getAllPairRows(array($candidateId, $reviewId), "reviews", array("candidate_id", "id"));

        if($data < 1)
            return $data;

        $params = array($jobTitle, $jobType, $employmentStatus, $pros, $cons, $rating);
        $paramsString = array("job_title", "job_type", "employment_status", "pros", "cons", "rating");

        $emptyArray = array_fill(0, count($params), '');
        $params = array_diff($params, $emptyArray);
        $paramsString = array_diff_key($paramsString, array_diff_key($paramsString, $params));

        $queryParams = implode(", ", array_map(fn($item) => $item . " = ?", $paramsString));
        array_push($params, $reviewId);
        $params = array_values($params);

        $stmt = $this->connect()->prepare("UPDATE reviews SET " . $queryParams . " WHERE id = ?;");

        if(!$stmt->execute($params))
        {
            $stmt = null;
            return 0;
        }

        $stmt = null;
        return 1;
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
            $response = $this->setNullValue($table, "end_month", $itemId);

            if($response < 1)
                return $response;
            
            $response = $this->setNullValue($table, "end_year", $itemId);

            if($response < 1)
                return $response;
        }

        if($description == "")
        {
            $stmt = null;
            $response = $this->setNullValue($table, "description", $itemId);

            if($response < 1)
                return $response;
        }

        $stmt = null;
        return 1;
    }
}
?>