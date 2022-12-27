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
            $user = $stmt->fetchAll(PDO::FETCH_ASSOC)[0];
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
}
?>