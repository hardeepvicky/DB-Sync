<?php
define("BASE_URL", '/DB-Sync/');
define("DEVELOPER", 'hardeep');

define("GIT_VERSION", git_version("../../../"));
define("BASE_PATH", '.sync/' . GIT_VERSION . "/");

define("SYNC_FILE", BASE_PATH . "sync.csv");
define("BASE_SQL_FILE", BASE_PATH . "database.sql");

define("SYNC_DEVELOPER_FILE", BASE_PATH . "developers/" . DEVELOPER . ".csv");

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