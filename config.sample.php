<?php
define("BASE_URL", '/DB-Sync/');
define("DEVELOPER", 'hardeep');

define("BASE_PATH", '.sync/');

define("SYNC_FILE", BASE_PATH . "sync.csv");

define("SYNC_DEVELOPER_FILE", BASE_PATH . "developers/" . DEVELOPER . ".csv");

define("SQL_LOCAL_CHANGE_ENABLE", true);

class config extends SharedConfig
{
    public static $database = array(
        "server" => "localhost",
        "username" => "root",
        "password" => "password",
        "database" => ""
    );
    
    public static function init()
    {
        require_once '../../Config/database.php';
        $databaseConfig = new DATABASE_CONFIG();
        
        self::$database['server'] = $databaseConfig->default["host"];
        self::$database['username'] = $databaseConfig->default["login"];
        self::$database['password'] = $databaseConfig->default["password"];
        self::$database['database'] = $databaseConfig->default["database"];
    }
}

//link to cakephp database file
config::init();