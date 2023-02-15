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
            $user = $stmt->fetch();
            $stmt = null;
            return $user;
        }

        $stmt = null;
        return -1;
    }

    protected function updateAbout($id, $text)
    {
        $oldAbout = $this->getCandidateAbout($id);
        if(is_array($oldAbout))
        {
            $query = "UPDATE candidate_about SET text = ? WHERE candidate_id = ?;";
            $params = array($text, $id);
        }
        else
        {
            $query = "INSERT INTO candidate_about VALUES (?, ?);";
            $params = array($id, $text);
        }

        $stmt = $this->connect()->prepare($query);

        if($text == "")
        {
            $stmt->bindValue(array_search($text, $params) + 1, null, PDO::PARAM_NULL);
            $stmt->bindValue(array_search($id, $params) + 1, $id, PDO::PARAM_INT);

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

    protected function getCandidateAbout($id)
    {
        $stmt = $this->connect()->prepare("SELECT text FROM candidate_about WHERE candidate_id = ?;");

        if(!$stmt->execute(array($id)))
        {
            $stmt = null;
            return 0;
        }

        if($stmt->rowCount() > 0)
        {
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC)[0];
            $stmt = null;
            return $data;
        }

        $stmt = null;
        return -1;
    }

    protected function createExperience($id, $title, $companyId, $companyName, $type, $startMonth, $startYear, $endMonth, $endYear, $description)
    {
        $params = array($title, $id, $companyName, $type, $startMonth, $startYear);
        $paramsString = array("title", "candidate_id", "company_name", "type", "start_month", "start_year");

        $optionalParams = array($companyId, $description, $endMonth, $endYear);
        $optionalParamsString = array("company_id", "description", "end_month", "end_year");

        for($i=0; $i<count($optionalParams); $i+=1)
            if(!empty($optionalParams[$i]))
            {
                array_push($params, $optionalParams[$i]);
                array_push($paramsString, $optionalParamsString[$i]);
            }

        $queryParams = implode(", ", $paramsString);
        $placeholders = implode(", ", array_fill(0, count($params), "?"));
        
        $stmt = $this->connect()->prepare("INSERT INTO candidate_experience (" . $queryParams . ") VALUES (" . $placeholders .");");

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
        $stmt = $this->connect()->prepare("SELECT * FROM candidate_experience WHERE id = ?;");
        
        if(!$stmt->execute(array($experienceId)))
        {
            $stmt = null;
            return 0;
        }

        if($stmt->rowCount() == 0)
        {
            $stmt = null;
            return -1;
        }
        
        //validating user
        $experience = $stmt->fetch();
        if($experience["candidate_id"] != $candidateId)
        {
            $stmt = null;
            return -2;
        }

        //validating dates
        if(is_null($experience["end_month"]) && is_null($experience["end_year"]))
        {
            if(((empty($endMonth) && !empty($endYear)) || (!empty($endMonth) && empty($endYear))) && !$ongoing)
            {
                $stmt = null;
                return -3;
            }
        }

        if(strlen($startMonth) > 3)
            $startMonth = substr($startMonth, 0, 3);
        if(strlen($endMonth) > 3)
            $endMonth = substr($endMonth, 0, 3);

        //handling parameters
        $params = array($title, $companyId, $companyName, $type, $startMonth, $startYear, $endMonth, $endYear, $description);
        $paramsString = array("title", "company_id", "company_name", "type", "start_month", "start_year", "end_month", "end_year", "description");
        $emptyArray = array('', '', '', '', '', '', '', '', '');
        
        $params = array_diff($params, $emptyArray);
        $paramsString = array_diff_key($paramsString, array_diff_key($paramsString, $params));

        //validating company
        if(!empty($companyId))
        {
            $stmt = null;
            $stmt = $this->connect()->prepare("SELECT company_name FROM companies WHERE id = ?;");

            if(!$stmt->execute(array($companyId)))
            {
                $stmt = null;
                return 0;
            }

            if($stmt->rowCount() == 0 || (!empty($companyName) && $stmt->fetch()["company_name"] != $companyName))
            {
                $stmt = null;
                return -4;
            }

            if($stmt->rowCount() > 0 && empty($companyName))
            {
                array_push($params, $stmt->fetch()["company_name"]);
                array_push($paramsString, "company_name");
            }
        }
        else if(!empty($companyName))
        {
            array_push($params, null);
            array_push($paramsString, "company_id");
        }

        //updating the database entry
        $queryParams = implode(", ", array_map(fn($item) => $item . " = ?", $paramsString));
        array_push($params, $experienceId);
        $params = array_values($params);
        
        if(count($params) > 1)
        {
            $stmt = null;
            $stmt = $this->connect()->prepare("UPDATE candidate_experience SET " . $queryParams . " WHERE id = ?;");

            if(!$stmt->execute($params))
            {
                $stmt = null;
                return 0;
            }
        }

        if($ongoing)
        {
            $stmt = null;
            $stmt = $this->connect()->prepare("UPDATE candidate_experience SET end_month = ?, end_year = ? WHERE id = ?;");
            $stmt->bindValue(1, null, PDO::PARAM_NULL);
            $stmt->bindValue(2, null, PDO::PARAM_NULL);
            $stmt->bindValue(3, $experienceId, PDO::PARAM_INT);

            if(!$stmt->execute())
            {
                $stmt = null;
                return 0;
            }
        }

        if($description == "")
        {
            $stmt = null;
            $stmt = $this->connect()->prepare("UPDATE candidate_experience SET description = ? WHERE id = ?;");
            $stmt->bindValue(1, null, PDO::PARAM_NULL);
            $stmt->bindValue(2, $experienceId, PDO::PARAM_INT);

            if(!$stmt->execute())
            {
                $stmt = null;
                return 0;
            }
        }

        $stmt = null;
        return 1;
    }

    protected function createEducation($id, $institutionName, $startMonth, $startYear, $endMonth, $endYear, $degree, $studyField, $description)
    {
        $params = array($id, $institutionName, $startMonth, $startYear,);
        $paramsString = array("candidate_id", "institution_name", "start_month", "start_year");

        $optionalParams = array($endMonth, $endYear, $degree, $studyField, $description);
        $optionalParamsString = array("end_month", "end_year", "degree", "study_field", "description");

        for($i=0; $i<count($optionalParams); $i+=1)
            if(!empty($optionalParams[$i]))
            {
                array_push($params, $optionalParams[$i]);
                array_push($paramsString, $optionalParamsString[$i]);
            }

        $queryParams = implode(", ", $paramsString);
        $placeholders = implode(", ", array_fill(0, count($params), "?"));
        
        $stmt = $this->connect()->prepare("INSERT INTO candidate_education (" . $queryParams . ") VALUES (" . $placeholders .");");

        if(!$stmt->execute($params))
        {
            $stmt = null;
            return 0;
        }

        $stmt = null;
        return 1;
    }

    protected function updateEducation($candidateId, $educationId, $institutionName, $startMonth, $startYear, $endMonth, $endYear, $degree, $studyField, $ongoing, $description)
    {
        $stmt = $this->connect()->prepare("SELECT * FROM candidate_education WHERE id = ?;");
        
        if(!$stmt->execute(array($educationId)))
        {
            $stmt = null;
            return 0;
        }

        if($stmt->rowCount() == 0)
        {
            $stmt = null;
            return -1;
        }
        
        //validating user
        $education = $stmt->fetch();
        if($education["candidate_id"] != $candidateId)
        {
            $stmt = null;
            return -2;
        }

        //validating dates
        if(is_null($education["end_month"]) && is_null($education["end_year"]))
        {
            if(((empty($endMonth) && !empty($endYear)) || (!empty($endMonth) && empty($endYear))) && !$ongoing)
            {
                $stmt = null;
                return -3;
            }
        }

        if(strlen($startMonth) > 3)
            $startMonth = substr($startMonth, 0, 3);
        if(strlen($endMonth) > 3)
            $endMonth = substr($endMonth, 0, 3);

        //handling parameters
        $params = array($institutionName, $startMonth, $startYear, $endMonth, $endYear, $degree, $studyField, $description);
        $paramsString = array("institution_name", "start_month", "start_year", "end_month", "end_year", "degree", "study_field", "description");
        $emptyArray = array('', '', '', '', '', '', '', '');
        
        $params = array_diff($params, $emptyArray);
        $paramsString = array_diff_key($paramsString, array_diff_key($paramsString, $params));

        //updating the database entry
        $queryParams = implode(", ", array_map(fn($item) => $item . " = ?", $paramsString));
        array_push($params, $educationId);
        $params = array_values($params);
        
        if(count($params) > 1)
        {
            $stmt = null;
            $stmt = $this->connect()->prepare("UPDATE candidate_education SET " . $queryParams . " WHERE id = ?;");

            if(!$stmt->execute($params))
            {
                $stmt = null;
                return 0;
            }
        }

        if($ongoing)
        {
            $stmt = null;
            $stmt = $this->connect()->prepare("UPDATE candidate_education SET end_month = ?, end_year = ? WHERE id = ?;");
            $stmt->bindValue(1, null, PDO::PARAM_NULL);
            $stmt->bindValue(2, null, PDO::PARAM_NULL);
            $stmt->bindValue(3, $educationId, PDO::PARAM_INT);

            if(!$stmt->execute())
            {
                $stmt = null;
                return 0;
            }
        }

        if($description == "")
        {
            $stmt = null;
            $stmt = $this->connect()->prepare("UPDATE candidate_education SET description = ? WHERE id = ?;");
            $stmt->bindValue(1, null, PDO::PARAM_NULL);
            $stmt->bindValue(2, $educationId, PDO::PARAM_INT);

            if(!$stmt->execute())
            {
                $stmt = null;
                return 0;
            }
        }

        $stmt = null;
        return 1;
    }

    protected function getSectionData($id, $table)
    {
        $stmt = $this->connect()->prepare("SELECT * FROM " . $table . " WHERE candidate_id = ?;");

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

    protected function deleteItem($candidateId, $itemId, $table)
    {
        $stmt = $this->connect()->prepare("SELECT candidate_id FROM " . $table . " WHERE id = ?;");
        
        if(!$stmt->execute(array($itemId)))
        {
            $stmt = null;
            return 0;
        }

        if($stmt->rowCount() == 0)
        {
            $stmt = null;
            return -1;
        }
        
        if($stmt->fetch()["candidate_id"] != $candidateId)
        {
            $stmt = null;
            return -2;
        }

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
        $stmt = $this->connect()->prepare("SELECT * FROM jobs WHERE id = ?;");

        if(!$stmt->execute(array($jobId)))
        {
            $stmt = null;
            return 0;
        }

        if($stmt->rowCount() == 0)
        {
            $stmt = null;
            return -1;
        }

        $stmt = null;
        $stmt = $this->connect()->prepare("SELECT * FROM " . $table . " WHERE candidate_id = ? AND job_id = ?;");

        if(!$stmt->execute(array($candidateId, $jobId)))
        {
            $stmt = null;
            return 0;
        }

        if($stmt->rowCount() > 0)
        {
            $stmt = null;
            return -2;
        }

        $stmt = null;
        $stmt = $this->connect()->prepare("INSERT INTO " . $table . " VALUES (?, ?);");

        if(!$stmt->execute(array($candidateId, $jobId)))
        {
            $stmt = null;
            return 0;
        }

        $stmt = null;
        return 1;
    }

    protected function getAppliedSavedHiddenJobs($id, $type)
    {
        $table = $type == "bookmarked" ? "bookmarks" : ($type == "hidden" ? "hidden" : "applicants");
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

    protected function deleteAppliedSavedHiddenJob($candidateId, $jobId, $type)
    {
        $table = $type == "bookmarked" ? "bookmarks" : ($type == "hidden" ? "hidden" : "applicants");
        $stmt = $this->connect()->prepare("SELECT * FROM " . $table . " WHERE candidate_id = ? AND job_id = ?;");
        
        if(!$stmt->execute(array($candidateId, $jobId)))
        {
            $stmt = null;
            return 0;
        }

        if($stmt->rowCount() == 0)
        {
            $stmt = null;
            return -1;
        }

        $stmt = null;
        $stmt = $this->connect()->prepare("DELETE FROM " . $table . " WHERE candidate_id = ? AND job_id = ?;");

        if(!$stmt->execute(array($candidateId, $jobId)))
        {
            $stmt = null;
            return 0;
        }

        $stmt = null;
        return 1;
    }
}
?>