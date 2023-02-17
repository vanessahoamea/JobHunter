<?php
class DBHandler
{
    protected function connect()
    {
        try
        {
            $dbh = new PDO("mysql:host=localhost; dbname=jobhunter", "root", "");
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
}
?>
