<?php
include_once("../controllers/company_controller.php");

class CompanyModel extends DBHandler
{
    protected function updateCompany($id, $cname, $email, $address, $website, $newPassword, $currentPassword)
    {
        $company = $this->getAllRows($id, "companies", "id");

        if($company == 0)
            return $company;

        $company = $company[0];
        if(!password_verify($currentPassword, $company["password"]))
            return -2;
        
        $encodedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $params = array($cname, $email);
        $paramsString = array("company_name", "email");
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
        array_push($params, $address);
        array_push($params, $website);
        array_push($params, $id);
        array_push($paramsString, "address");
        array_push($paramsString, "website");

        //modifying database entry
        $queryParams = implode(", ", array_map(fn($item) => $item . " = ?", $paramsString));
        $stmt = $this->connect()->prepare("UPDATE companies SET " . $queryParams . " WHERE id = ?;");

        if(!$stmt->execute($params))
        {
            $stmt = null;
            return 0;
        }

        if($address == "")
        {
            $stmt = null;
            $response = $this->setNullValue("companies", "address", $id);

            if($response < 1)
                return $response;
        }

        if($website == "")
        {
            $stmt = null;
            $response = $this->setNullValue("companies", "website", $id);

            if($response < 1)
                return $response;
        }

        $stmt = null;
        return 1;
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

    protected function createJob($id, $title, $skills, $type, $level, $locationName, $locationCoords, $physical, $salary, $question1, $question2, $question3, $datePosted)
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

        $questions = array($question1, $question2, $question3);
        for($i=0; $i<3; $i+=1)
            if(!empty($questions[$i]))
            {
                array_push($params, $questions[$i]);
                array_push($paramsString, "question" . ($i+1));
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

    protected function getSingleJob($jobId)
    {
        $stmt = $this->connect()->prepare("SELECT jobs.*, companies.company_name FROM jobs JOIN companies ON jobs.company_id = companies.id WHERE jobs.id = ?;");

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

    protected function getRecentJobs($id, $page, $limit, $keywords, $locationLat, $locationLon, $skills, $type, $level, $salary, $candidateId)
    {
        $result = array();
        $startIndex = ($page - 1) * $limit;
        $endIndex = $page * $limit;

        $params = array();
        $queryParams = array();

        if($id > 0)
        {
            array_push($params, $id);
            array_push($queryParams, "company_id = ?");
        }

        if(!empty($keywords))
        {
            $options = array();
            foreach($keywords as $value)
            {
                array_push($params, "%" . $value . "%");
                array_push($options, "LOWER(title) LIKE ?");
            }
            array_push($queryParams, "(" . implode(" OR ", $options) . ")");
        }

        if(!empty($locationLat) && !empty($locationLon))
        {
            array_push($params, "\"" . $locationLat . "\"", "\"" . $locationLon . "\"");
            array_push($queryParams, "JSON_CONTAINS(location_coords, ?, '$[0]') AND JSON_CONTAINS(location_coords, ?, '$[1]')");
        }

        if(!empty($skills))
        {
            $options = array();
            foreach($skills as $value)
            {
                array_push($params, "\"" . $value . "\"");
                array_push($options, "JSON_CONTAINS(skills, ?)");
            }
            array_push($queryParams, "(" . implode(" OR ", $options) . ")");
        }

        if(!empty($type))
        {
            $options = array();
            foreach($type as $value)
            {
                array_push($params, $value);
                array_push($options, "?");
            }
            array_push($queryParams, "type IN (" . implode(", ", $options) . ")");
        }

        if(!empty($level))
        {
            $options = array();
            foreach($level as $value)
            {
                array_push($params, $value);
                array_push($options, "?");
            }
            array_push($queryParams, "level IN (" . implode(", ", $options) . ")");
        }

        if($salary == "true")
            array_push($queryParams, "salary IS NOT NULL");

        $query = "SELECT jobs.*, companies.company_name FROM jobs JOIN companies ON jobs.company_id = companies.id";
        $query .= count($queryParams) > 0 ? " WHERE " . implode(" AND ", $queryParams) : "";
        if($candidateId != 0)
        {
            $query .= count($queryParams) > 0 ? " AND " : " WHERE ";
            $query .= "jobs.id NOT IN (SELECT job_id FROM hidden WHERE candidate_id = ?)";
            array_push($params, $candidateId);
        }
        $query .= " ORDER BY date_posted DESC, jobs.id DESC;";
        $stmt = $this->connect()->prepare($query);

        if(!$stmt->execute($params))
        {
            $stmt = null;
            return 0;
        }

        $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $jobs = array_slice($jobs, $startIndex, $limit);
        for($i=0; $i<count($jobs); $i+=1)
        {
            unset($jobs[$i]["email"]);
            unset($jobs[$i]["address"]);
            unset($jobs[$i]["password"]);
        }

        $result["total_count"] = $stmt->rowCount();
        if($startIndex > 0)
            $result["previous"] = $page - 1;
        if($endIndex < $stmt->rowCount())
            $result["next"] = $page + 1;
        $result["jobs"] = $jobs;

        $stmt = null;
        return $result;
    }

    protected function updateJob($companyId, $jobId, $title, $skills, $type, $level, $locationName, $locationCoords, $salary, $physical, $question1, $question2, $question3)
    {
        $job = $this->getAllPairRows(array($companyId, $jobId), "jobs", array("company_id", "id"));
        
        if($job < 1)
            return $job;
        
        if((!empty($locationName) && empty($locationCoords)) || (empty($locationName) && !empty($locationCoords)))
            return -2;

        //handling parameters
        $params = array($title, $skills, $type, $level, $locationName, $locationCoords, $salary, $physical, $question1, $question2, $question3);
        $paramsString = array("title", "skills", "type", "level", "location_name", "location_coords", "salary", "physical", "question1", "question2", "question3");
        $emptyArray = array('', '', '', '', '', '', '', '', '', '', '');
        
        $params = array_diff($params, $emptyArray);
        $paramsString = array_diff_key($paramsString, array_diff_key($paramsString, $params));

        //updating the database entry
        $queryParams = implode(", ", array_map(fn($item) => $item . " = ?", $paramsString));
        array_push($params, $jobId);
        $params = array_values($params);

        if(count($params) > 1)
        {
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
            $response = $this->setNullValue("jobs", "skills", $jobId);

            if($response < 1)
                return $response;
        }

        if($salary == "")
        {
            $stmt = null;
            $response = $this->setNullValue("jobs", "salary", $jobId);

            if($response < 1)
                return $response;
        }

        $questions = array($question1, $question2, $question3);
        for($i=0; $i<3; $i+=1)
            if($questions[$i] == "")
            {
                $stmt = null;
                $response = $this->setNullValue("jobs", "question" . ($i+1), $jobId);

                if($response < 1)
                    return $response;
            }

        $stmt = null;
        return 1;
    }

    protected function deleteJob($companyId, $jobId)
    {
        $job = $this->getAllPairRows(array($companyId, $jobId), "jobs", array("company_id", "id"));
        
        if($job < 1)
            return $job;

        //delete entries that reference this job
        $stmt = $this->connect()->prepare("DELETE FROM applicants WHERE job_id = ?; DELETE FROM bookmarks WHERE job_id = ?; DELETE FROM hidden WHERE job_id = ?;");

        if(!$stmt->execute(array($jobId, $jobId, $jobId)))
        {
            $stmt = null;
            return 0;
        }

        //delete job
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
        $job = $this->getAllPairRows(array($companyId, $jobId), "jobs", array("company_id", "id"));
        
        if($job < 1)
            return $job;

        $stmt = $this->connect()->prepare("SELECT candidates.id, candidates.first_name, candidates.last_name, applicants.question1_answer, applicants.question2_answer, applicants.question3_answer FROM applicants JOIN candidates ON applicants.candidate_id = candidates.id WHERE job_id = ?;");

        if(!$stmt->execute(array($jobId)))
        {
            $stmt = null;
            return 0;
        }

        $applicants = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = null;
        return $applicants;
    }

    protected function getSingleReview($reviewId)
    {
        $stmt = $this->connect()->prepare("SELECT * FROM reviews WHERE id = ?;");

        if(!$stmt->execute(array($reviewId)))
        {
            $stmt = null;
            return 0;
        }

        if($stmt->rowCount() == 0)
        {
            $stmt = null;
            return -1;
        }

        $review = $stmt->fetchAll(PDO::FETCH_ASSOC)[0];
        $stmt = null;
        return $review;
    }

    protected function getApplicantNotifications($companyId)
    {
        $stmt = $this->connect()->prepare("SELECT DISTINCT date FROM notifications WHERE company_id = ? ORDER BY date DESC;");

        if(!$stmt->execute(array($companyId)))
        {
            $stmt = null;
            return 0;
        }

        $dates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $dates = array_map(fn($value) => $value["date"], $dates);
        $stmt = null;

        $stmt = $this->connect()->prepare("SELECT * FROM notifications WHERE company_id = ?;");

        if(!$stmt->execute(array($companyId)))
        {
            $stmt = null;
            return 0;
        }

        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = null;

        $notificationsByDate = array();
        foreach($dates as $date)
        {
            $notificationsByDate[$date] = array_filter($notifications, fn($notification) => $notification["date"] == $date);
            $notificationsByDate[$date] = array_values($notificationsByDate[$date]);
        }

        return $notificationsByDate;
    }

    protected function deleteNotifications($companyId)
    {
        $stmt = $this->connect()->prepare("UPDATE notifications SET unread_notifications = 0 WHERE company_id = ?;");

        if(!$stmt->execute(array($companyId)))
        {
            $stmt = null;
            return 0;
        }

        $stmt = null;
        return 1;
    }
}
?>