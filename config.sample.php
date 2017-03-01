<?php
define("BASE_URL", '/dbsync/');
define("DEVELOPER", 'hardeep');

define("GIT_VERSION", git_version("./"));
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
        "database" => "test_schema"
    );
}