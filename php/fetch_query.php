<?php 
if (!empty($_POST) && isset($_GET['write_query_to_csv']))
{
    $logs = $_POST['log'];
    
    foreach($logs as $k => $log)
    {
        if(!isset($log["will_execute"]))
        {
            $log["will_execute"] = 0;
        }
        
        $logs[$k] = $log;
    }
    
    if (CsvUtility::writeCSV(SYNC_DEVELOPER_FILE, $logs, true, ",", "a"))
    {
        Session::writeFlash("success", "Queries are wrtten to " . DEVELOPER . ".csv File");
    }
    else
    {
        Session::writeFlash("failure", "Failed to write Queries.");
    }
    
    header('Location:' . config::url("fetch_query"));
    die();
}

$or = array(
    array(
        "field" => "argument",
        "op" => "LIKE",
        "value" => "CREATE%"
    ),
    array(
        "field" => "argument",
        "op" => "LIKE",
        "value" => "ALTER%"
    ),
    array(
        "field" => "argument",
        "op" => "LIKE",
        "value" => "DROP%"
    ),
    array(
        "field" => "argument",
        "op" => "LIKE",
        "value" => "USE%"
    ),
);

if (isset(config::$dml_tables) && !empty(config::$dml_tables))
{
    $or[] = array(
        "field" => "argument",
        "op" => "LIKE",
        "value" => "INSERT%"
    );
    
    $or[] = array(
        "field" => "argument",
        "op" => "LIKE",
        "value" => "UPDATE%"
    );
    
    $or[] = array(
        "field" => "argument",
        "op" => "LIKE",
        "value" => "DELETE%"
    );
    
    $or[] = array(
        "field" => "argument",
        "op" => "LIKE",
        "value" => "TRUNCATE%"
    );
}

$conditions = array(
    "AND" => array(
        0 => array(
            "OR" => array(
                array(
                    "field" => "command_type",
                    "value" => "Query"
                )
            )
        ),
        1 => array(
            "OR" => $or
        )
    )
);

if (isset(config::$dml_tables) && !empty(config::$dml_tables))
{
    $conditions["AND"][0]["OR"][] = array(
        "field" => "command_type",
        "value" => "Execute"
    );
}

$sync_data = CsvUtility::fetchCSV(SYNC_DEVELOPER_FILE);

if ($sync_data == false)
{
    $sync_data = array();
}

$last_sync = end($sync_data);

$last_sync_on = false;
if($last_sync)
{
    $last_sync_on = $last_sync['datetime'];
    
    $conditions["AND"][] = array(
        "field" => "event_time",
        "op" => ">",
        "value" => $last_sync_on
    );
}

$where = get_where($conditions);    

$q = "SELECT event_time, argument FROM mysql.general_log where $where order by event_time ASC;";
$logs = $mysql->select($q);

$current_db_set_default = false;
$db = config::$database['database'];
//debug($logs); exit;
$sp_list = $view_list = $function_list = array();
foreach($logs as $k => $log)
{
    $log['argument'] = trim(preg_replace('/\s+/', ' ', $log['argument']));
    $arg = $log['argument'];
    
    $arg_temp = str_replace("`", "", $arg);
    
    if (str_contain($arg_temp, "USE", 0, strlen("USE")))
    {
        if ($arg_temp == "USE $db")
        {
            $current_db_set_default = true;
        }
        else
        {
            $current_db_set_default = false;
        }
        
        unset($logs[$k]);
        continue;
    }
    else
    {
        $ddl_type = $continue = get_DDL_query_type($arg_temp, $db, $current_db_set_default == false ? true : false);
        
        if (isset(config::$dml_tables) && !empty(config::$dml_tables))
        {   
            foreach(config::$dml_tables as $t)
            {
                if (get_DML_query_type($arg_temp, $t, $db, $current_db_set_default == false ? true : false))
                {
                    $continue = true;
                }
            }
        }
        
        if ($continue === false)
        {
            unset($logs[$k]);
            continue;
        }
        
        if (str_contain($ddl_type, "PROCEDURE") || str_contain($ddl_type, "FUNCTION") || str_contain($ddl_type, "VIEW"))
        {
            $arg = str_replace("DEFINER=`root`@`localhost`", "", $arg);
            $arg = str_replace("DEFINER= `root`@`localhost`", "", $arg);
            $arg = str_replace("DEFINER =`root`@`localhost`", "", $arg);
            $arg = str_replace("SQL SECURITY DEFINER", "", $arg);
            $arg = str_replace("ALGORITHM=UNDEFINED", "", $arg);
            $arg = str_replace("ALGORITHM= UNDEFINED", "", $arg);
            $arg = str_replace("ALGORITHM = UNDEFINED", "", $arg);
        }
    }
    
    $arg = str_replace("`$db`.", "", $arg);
    $arg = str_replace("$db.", "", $arg);
    
    $arg = trim(preg_replace('/\s+/', ' ', $arg));
    
    $log['argument'] = $arg;
    
    if (str_contain($ddl_type, "PROCEDURE") || str_contain($ddl_type, "FUNCTION") || str_contain($ddl_type, "VIEW"))
    {
        $temp_arg = str_replace("IF EXISTS", "", $arg);
        $temp_arg = trim(preg_replace('/\s+/', ' ', $temp_arg));
        unset($logs[$k]);
        
        if (str_contain($ddl_type, "PROCEDURE"))
        {
            $name = get_name_from_ddl_sql($temp_arg, $ddl_type);

            if ($name)
            {
                $sp_list[$name][$ddl_type] = $log;
            }
        }
        else if (str_contain($ddl_type, "FUNCTION"))
        {
            $name = get_name_from_ddl_sql($temp_arg, $ddl_type);

            if ($name)
            {
                $function_list[$name][$ddl_type] = $log;
            }
        }
        else if (str_contain($ddl_type, "VIEW"))
        {
            $name = get_name_from_ddl_sql($temp_arg, $ddl_type);

            if ($name)
            {
                $view_list[$name][$ddl_type] = $log;
            }            
        }
    }
    else
    {
        $logs[$k] = $log;
    }
}

foreach($sp_list as $name => $ddl_types)
{
    if (isset($ddl_types['DROP PROCEDURE']))
    {
        $logs[] = $ddl_types['DROP PROCEDURE'];
    }
    
    if (isset($ddl_types['CREATE PROCEDURE']))
    {
        $logs[] = $ddl_types['CREATE PROCEDURE'];
    }
}

foreach($view_list as $name => $ddl_types)
{
    if (isset($ddl_types['DROP VIEW']))
    {
        $logs[] = $ddl_types['DROP VIEW'];
    }
    
    if (isset($ddl_types['CREATE VIEW']))
    {
        $logs[] = $ddl_types['CREATE VIEW'];
    }
    
    if (isset($ddl_types['CREATE OR REPLACE']))
    {
        $logs[] = $ddl_types['CREATE OR REPLACE'];
    }
}

foreach($function_list as $name => $ddl_types)
{
    if (isset($ddl_types['DROP FUNCTION']))
    {
        $logs[] = $ddl_types['DROP FUNCTION'];
    }
    
    if (isset($ddl_types['CREATE FUNCTION']))
    {
        $logs[] = $ddl_types['CREATE FUNCTION'];
    }
}

$id = $last_sync ? ($last_sync["id"] + 1) : 1;
foreach($logs as $k => $log)
{
    $logs[$k] = array(
        'id' => $id,
        'datetime' => $log['event_time'],
        'query' => $log['argument']
    );
    $id++;
}