<?php
class DBHandler
{
    protected function connect()
    {
        try
        {
            $dbh = new PDO("mysql:host=localhost; dbname=jobhunter", "root", "", array(PDO::MYSQL_ATTR_FOUND_ROWS => true));
            return $dbh;
        }
        catch(PDOException $e)
        {
            echo "Error: " . $e.getMessage();
            die();
        }
    }

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

    protected function getAllPairRows($params, $table, $fields)
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

    protected function setNullValue($table, $column, $itemId)
    {
        $stmt = $this->connect()->prepare("UPDATE " . $table . " SET " . $column . " = ? WHERE id = ?;");
        $stmt->bindValue(1, null, PDO::PARAM_NULL);
        $stmt->bindValue(2, $itemId, PDO::PARAM_INT);

        if(!$stmt->execute())
        {
            $stmt = null;
            return 0;
        }

        $stmt = null;
        return 1;
    }

    protected function getReviews($id, $page, $limit, $field)
    {
        $result = array();
        $startIndex = ($page - 1) * $limit;
        $endIndex = $page * $limit;

        $stmt = $this->connect()->prepare("SELECT reviews.*, companies.company_name FROM reviews JOIN companies ON reviews.company_id = companies.id WHERE " . $field . " = ? ORDER BY date_posted DESC, id DESC;");

        if(!$stmt->execute(array($id)))
        {
            $stmt = null;
            return 0;
        }

        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $reviews = array_slice($reviews, $startIndex, $limit);

        $result["total_count"] = $stmt->rowCount();
        if($startIndex > 0)
            $result["previous"] = $page - 1;
        if($endIndex < $stmt->rowCount())
            $result["next"] = $page + 1;
        $result["reviews"] = $reviews;

        $stmt = null;
        return $result;
    }
}
?>
