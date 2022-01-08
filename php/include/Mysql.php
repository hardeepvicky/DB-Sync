<?php

class Mysql
{
    public static $queryLog = true;
    public static $conn, $db, $logs;
    
    public function __construct($config)
    {
        $config['port'] = $config['port'] ?? 3306;
        self::$db = $config['database'];
        self::$conn = mysqli_connect($config['server'], $config['username'], $config['password'], $config['database'], $config['port']);
    }
    
    public function query($q)
    {
        self::$logs[] = $q;
        
        return mysqli_query(self::$conn, $q);
    }
    
    public function select($q)
    {
        $result = $this->query($q);
        
        $records = array();
        
        while($row = mysqli_fetch_assoc($result))
        {
            $records[] = $row;
        }
        
        return $records;
    }
    
    public function transactionBegin()
    {
        $this->query("SET AUTOCOMMIT=0");
        $this->query("START TRANSACTION");
    }
    
    public function transactionCommit()
    {
        $this->query("COMMIT");
    }
    
    public function transactionRollback()
    {
        $this->query("ROLLBACK");
    }
}
