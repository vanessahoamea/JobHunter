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

    protected function createExperience($id, $title, $companyId, $companyName, $type, $startMonth, $startYear, $endMonth, $endYear, $description)
    {
        $params = array($title, $id, $companyName, $type, $startMonth, $startYear);
        $paramsString = array("title", "candidate_id", "company_name", "type", "start_month", "start_year");

        if(!empty($companyId))
        {
            array_push($params, $companyId);
            array_push($paramsString, "company_id");
        }
        if(!empty($description))
        {
            array_push($params, $description);
            array_push($paramsString, "description");
        }
        if(!empty($endMonth))
        {
            array_push($params, $endMonth);
            array_push($paramsString, "end_month");
        }
        if(!empty($endYear))
        {
            array_push($params, $endYear);
            array_push($paramsString, "end_year");
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

    protected function getAllExperience($id)
    {
        $stmt = $this->connect()->prepare("SELECT * FROM candidate_experience WHERE candidate_id = ?;");

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

    protected function deleteExperience($candidateId, $experienceId)
    {
        $stmt = $this->connect()->prepare("SELECT candidate_id FROM candidate_experience WHERE id = ?;");
        
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
        
        if($stmt->fetch()["candidate_id"] != $candidateId)
        {
            $stmt = null;
            return -2;
        }

        $stmt = null;
        $stmt = $this->connect()->prepare("DELETE FROM candidate_experience WHERE id = ?;");

        if(!$stmt->execute(array($experienceId)))
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

    protected function getJobs($id, $type)
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