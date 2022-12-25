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
}
?>
