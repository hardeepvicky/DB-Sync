<?php 
if (isset($_GET['write_query_to_csv']))
{
    $data = $_POST['data'];
    
    $max_datetime = false;
    $max_id = 1;
    foreach($data as $k => $log)
    {
        if (!isset($log["will_execute"]))
        {
            $log["will_execute"] = 0;
        }
        
        if ($max_datetime === false)
        {
            $max_datetime = $log["datetime"];
        }
        else if (DateUtility::compare($log["datetime"], $max_datetime) > 0)
        {
            $max_datetime = DateUtility::getDate($log["datetime"], DateUtility::DATETIME_FORMAT);
        }
        
        if ($log['id'] > $max_id)
        {
            $max_id = $log['id'];
        }
        
        $logs[$k] = array(
            "id" => $log["id"],
            "datetime" => $log["datetime"],
            "query" => $log["query"],
            "will_execute" => $log["will_execute"],
        );
    }
    
    try
    {
        $fetch_developer_utility = new csv\CsvUtility(FETCH_DEVELOPER_FILE);
        $fetch_developer_utility->write($logs, "a");
        
        $fetch_utility = new csv\CsvUtility(FETCH_LOG_FILE);
        $fetch_data = $fetch_utility->find();

        $is_found = false;
        if ($fetch_data)
        {
            foreach($fetch_data as $a => $arr)
            {
                if ($arr['name'] == DEVELOPER)
                {
                    $is_found = true;
                    $fetch_data[$a]["last_sync_datetime"] = $max_datetime;
                    $fetch_data[$a]["last_sync_id"] = $max_id;
                }
            }
        }

        if ( !$is_found )
        {
            $fetch_data[] = [
                "name" => DEVELOPER,
                "last_sync_datetime" => $max_datetime,
                "last_sync_id" => $max_id
            ];
        }

        $fetch_utility->write($fetch_data);
        Session::writeFlash("success", "Queries are wrtten to " . DEVELOPER . ".csv File");
    }
    catch(Exception $ex)
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

$fetch_log_utility = new csv\CsvUtility(FETCH_LOG_FILE);
$where = new csv\CsvWhere("name", "", DEVELOPER);
$fetch_log = $fetch_log_utility->find([], [$where]);

$last_sync_on = "";
if($fetch_log)
{
    $fetch_log = reset($fetch_log);
    $last_sync_on = $fetch_log['last_sync_datetime'];
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
$str_to_replace = array(
    "SQL SECURITY DEFINER", 
    "ALGORITHM=UNDEFINED", "ALGORITHM =UNDEFINED", "ALGORITHM= UNDEFINED", "ALGORITHM = UNDEFINED",
    "DEFINER=`root`@`localhost`","DEFINER =`root`@`localhost`","DEFINER= `root`@`localhost`","DEFINER = `root`@`localhost`",
);
    
foreach($logs as $k => $log)
{
    $ddl_type = "";
    $arg = $log['argument'];
    
    $arg = trim(preg_replace('/\s+/', ' ', $arg));
    foreach($str_to_replace as $rep_str)
    {
        $pos = strpos($arg, $rep_str);
        if ($pos !== false)
        {
            $arg = str_replace(substr($arg, $pos, strlen($rep_str)), "", $arg);
        }
    }
    
    $arg = trim(preg_replace('/\s+/', ' ', $arg));
    $arg_temp = strtoupper(str_replace("`", "", $arg));
    
    if (str_contain($arg_temp, "USE", 0, strlen("USE")))
    {
        if ($arg_temp == "USE " . strtoupper($db))
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
    }
    
    $arg = str_replace("`$db`.", "", $arg);
    $arg = str_replace("$db.", "", $arg);
    
    $log['argument'] = $arg;
    
    if (str_contain($ddl_type, "PROCEDURE") || str_contain($ddl_type, "FUNCTION") || str_contain($ddl_type, "VIEW"))
    {
        $arg_temp = str_replace("IF EXISTS", "", $arg_temp);
        unset($logs[$k]);
        
        if (str_contain($ddl_type, "PROCEDURE"))
        {
            $name = get_name_from_ddl_sql($arg_temp, $ddl_type);
            if ($name)
            {
                $name = str_replace("$db.", "", $name);
                $sp_list[$name][$ddl_type] = $log;
            }
        }
        else if (str_contain($ddl_type, "FUNCTION"))
        {
            $name = get_name_from_ddl_sql($arg_temp, $ddl_type);

            if ($name)
            {
                $name = str_replace("$db.", "", $name);
                $function_list[$name][$ddl_type] = $log;
            }
        }
        else if (str_contain($ddl_type, "VIEW"))
        {
            $name = get_name_from_ddl_sql($arg_temp, $ddl_type);

            if ($name)
            {
                $name = str_replace("$db.", "", $name);
                $view_list[$name][$ddl_type] = $log;
            }            
        }
    }
    else
    {
        $logs[$k] = $log;
    }
}

$routines = $sp_list + $view_list + $function_list;

foreach($routines as $name => $arr)
{
    foreach($arr as $inner_arr)
    {
        $logs[] = $inner_arr;
    }
}

usort($logs, function ($a, $b)
{
    $a_time = strtotime($a["event_time"]);
    $b_time = strtotime($b["event_time"]);
    
    return $a_time < $b_time ? -1 : $a_time > $b_time ? 1 : 0;
});

$id = 1;

if ($fetch_log && isset($fetch_log['last_sync_id']))
{
    $id = $fetch_log['last_sync_id'] + 1;
}

foreach($logs as $k => $log)
{
    $logs[$k] = array(
        'id' => $id,
        'datetime' => $log['event_time'],
        'query' => $log['argument']
    );
    $id++;
}