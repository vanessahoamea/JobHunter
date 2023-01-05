<?php
include_once("../controllers/company_controller.php");

class CompanyModel extends DBHandler
{
    protected function getAllData($id)
    {
        $stmt = $this->connect()->prepare("SELECT company_name, email, address FROM companies WHERE id = ?;");

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

    protected function getAllCompanyNames($companyName)
    {
        $stmt = $this->connect()->prepare("SELECT id, company_name FROM companies WHERE LOWER(company_name) LIKE ? LIMIT 5;");

        if(!$stmt->execute(array(strtolower($companyName) . "%")))
        {
            $stmt = null;
            return 0;
        }

        $names = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = null;
        return $names;
    }

    protected function createJob($id, $title, $skills, $type, $level, $locationName, $locationCoords, $physical, $salary, $datePosted)
    {
        $params = array($id, $title, $type, $level, $locationName, $locationCoords, $physical, $datePosted);
        $paramsString = array("company_id", "title", "type", "level", "location_name", "location_coords", "physical", "date_posted");

        if(!empty($skills))
        {
            array_push($params, $skills);
            array_push($paramsString, "skills");
        }
        if(!empty($salary))
        {
            array_push($params, $salary);
            array_push($paramsString, "salary");
        }

        $queryParams = implode(", ", $paramsString);
        $placeholders = implode(", ", array_fill(0, count($params), "?"));

        $conn = $this->connect();
        $stmt = $conn->prepare("INSERT INTO jobs (" . $queryParams . ") VALUES (" . $placeholders .");");

        if(!$stmt->execute($params))
        {
            $conn = null;
            return 0;
        }

        $id = $conn->lastInsertId();
        $conn = null;
        return $id;
    }

    public function getSingleJob($jobId)
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

        $job = $stmt->fetchAll(PDO::FETCH_ASSOC)[0];
        $stmt = null;
        return $job;
    }

    protected function getRecentJobs($id, $page, $limit)
    {
        $result = array();
        $startIndex = ($page - 1) * $limit;
        $endIndex = $page * $limit;

        if($id > 0)
        {
            $query = "SELECT * FROM jobs WHERE company_id = ? ORDER BY date_posted DESC, id DESC;";
            $params = array($id);
        }
        else
        {
            $query = "SELECT * FROM jobs ORDER BY date_posted DESC, id DESC;";
            $params = array();
        }
        $stmt = $this->connect()->prepare($query);

        if(!$stmt->execute($params))
        {
            $stmt = null;
            return 0;
        }

        $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $jobs = array_slice($jobs, $startIndex, $limit);

        $result["total_count"] = $stmt->rowCount();
        if($startIndex > 0)
            $result["previous"] = $page - 1;
        if($endIndex < $stmt->rowCount())
            $result["next"] = $page + 1;
        $result["jobs"] = $jobs;

        $stmt = null;
        return $result;
    }

    protected function updateJob($companyId, $jobId, $title, $skills, $type, $level, $locationName, $locationCoords, $salary, $physical)
    {
        $stmt = $this->connect()->prepare("SELECT company_id FROM jobs WHERE id = ?;");

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

        if($stmt->fetch()["company_id"] != $companyId)
        {
            $stmt = null;
            return -2;
        }
        
        if((!empty($locationName) && empty($locationCoords)) || (empty($locationName) && !empty($locationCoords)))
            return -3;

        //handling parameters
        $params = array($title, $skills, $type, $level, $locationName, $locationCoords, $salary, $physical);
        $paramsString = array("title", "skills", "type", "level", "location_name", "location_coords", "salary", "physical");
        $emptyArray = array('', '', '', '', '', '', '', '');
        
        $params = array_diff($params, $emptyArray);
        $paramsString = array_diff_key($paramsString, array_diff_key($paramsString, $params));

        //updating the database entry
        $queryParams = implode(", ", array_map(fn($item) => $item . " = ?", $paramsString));
        array_push($params, $jobId);
        $params = array_values($params);

        if(count($params) > 1)
        {
            $stmt = null;
            $stmt = $this->connect()->prepare("UPDATE jobs SET " . $queryParams . " WHERE id = ?;");

            if(!$stmt->execute($params))
            {
                $stmt = null;
                return 0;
            }
        }

        if($skills != null && $skills == "")
        {
            $stmt = null;
            $stmt = $this->connect()->prepare("UPDATE jobs SET skills = ? WHERE id = ?;");
            $stmt->bindValue(1, null, PDO::PARAM_NULL);
            $stmt->bindValue(2, $jobId, PDO::PARAM_INT);

            if(!$stmt->execute())
            {
                $stmt = null;
                return 0;
            }
        }

        if($salary == "")
        {
            $stmt = null;
            $stmt = $this->connect()->prepare("UPDATE jobs SET salary = ? WHERE id = ?;");
            $stmt->bindValue(1, null, PDO::PARAM_NULL);
            $stmt->bindValue(2, $jobId, PDO::PARAM_INT);

            if(!$stmt->execute())
            {
                $stmt = null;
                return 0;
            }
        }

        $stmt = null;
        return 1;
    }

    protected function deleteJob($companyId, $jobId)
    {
        $stmt = $this->connect()->prepare("SELECT company_id FROM jobs WHERE id = ?;");

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

        if($stmt->fetch()["company_id"] != $companyId)
        {
            $stmt = null;
            return -2;
        }

        $stmt = null;
        $stmt = $this->connect()->prepare("DELETE FROM jobs WHERE id = ?;");

        if(!$stmt->execute(array($jobId)))
        {
            $stmt = null;
            return 0;
        }

        $stmt = null;
        return 1;
    }

    protected function getApplicants($companyId, $jobId)
    {
        $stmt = $this->connect()->prepare("SELECT company_id FROM jobs WHERE id = ?;");

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

        if($stmt->fetch()["company_id"] != $companyId)
        {
            $stmt = null;
            return -2;
        }

        $stmt = null;
        $stmt = $this->connect()->prepare("SELECT candidates.id, candidates.first_name, candidates.last_name FROM applicants JOIN candidates ON applicants.candidate_id = candidates.id WHERE job_id = ?;");

        if(!$stmt->execute(array($jobId)))
        {
            $stmt = null;
            return 0;
        }

        $applicants = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = null;
        return $applicants;
    }

    protected function validateJob($companyId, $jobId)
    {
        $stmt = $this->connect()->prepare("SELECT company_id FROM jobs WHERE id = ?;");

        if(!$stmt->execute(array($jobId)) || $stmt->rowCount() == 0)
        {
            $stmt = null;
            return false;
        }

        if($stmt->fetch()["company_id"] != $companyId)
        {
            $stmt = null;
            return false;
        }

        $stmt = null;
        return true;
    }
}
?>