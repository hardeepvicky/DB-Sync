<?php

function debug($data)
{
    $bt = debug_backtrace();
    $caller = array_shift($bt);
    
    echo "<pre>";
    echo "<b>" . $caller["file"] . " : " . $caller["line"] . "</b><br/>";
    print_r($data);
    echo "</pre>";
}

/**
 * return array to where sql string
 * @param type $conditions
 * @return string
 */
function get_where($conditions)
{
    $where = array();
    
    $raw_where = '';
    
    foreach($conditions as $operator => $data)
    {
        foreach($data as $arr)
        {
            if (isset($arr["field"]) && isset($arr["value"]))
            {
                $arr["op"] = isset($arr["op"]) ? $arr["op"] : "=";
                
                $where[] = $arr["field"] . " " . $arr["op"] . " '" . $arr["value"] . "'";
            }
            else
            {
                $where[] = get_where($arr);
            }            
        }
        
        $raw_where .= "(" . implode(" $operator ",  $where) . ")";
    }
    
    return $raw_where;
}

function str_contain($str, $needle, $start = false, $end = false)
{
    $str = strtolower(trim($str));
    $needle = strtolower(trim($needle));
    
    if ($start !== false)
    {
        $str = substr($str, $start);
    }
    
    if ($end !== false)
    {
        $str = substr($str, 0, $end);
    }
    
    return strpos($str, $needle) !== false;
}

function get_DDL_query_type($query, $db, $db_required)
{
    $query = strtoupper($query);
    
    if (!$db_required)
    {
        $find = "CREATE TABLE";
        if (str_contain($query, $find, 0, strlen($find)))
        {
            return "TABLE";
        }
    }
    
    $find = "CREATE TABLE";
    $find = $db ? $find . " $db." : $find;
    if (str_contain($query, $find, 0, strlen($find)))
    {
        return "TABLE";
    }
    
    if (!$db_required)
    {
        $find = "ALTER TABLE";
        if (str_contain($query, $find, 0, strlen($find)))
        {
            return "TABLE";
        }
    }
    
    $find = "ALTER TABLE";
    $find = $db ? $find . " $db." : $find;
    if (str_contain($query, $find, 0, strlen($find)))
    {
        return "TABLE";
    }
    
    if (!$db_required)
    {
        $find = "DROP TABLE";
        if (str_contain($query, $find, 0, strlen($find)))
        {
            return "TABLE";
        }
    }
    
    $find = "DROP TABLE";
    $find = $db ? $find . " $db." : $find;
    if (str_contain($query, $find, 0, strlen($find)))
    {
        return "TABLE";
    }
    
    $q = substr($query, 0, strpos($query, "AS"));
    if ((
            str_contain($q, "CREATE", 0, strlen('CREATE')) 
            || str_contain($q, "ALTER", 0, strlen('ALTER'))
        ) 
            && str_contain($q, "VIEW")
      )
    {
        if ($db_required)
        {
            if (str_contain($q, "$db."))
            {
                return "VIEW";
            }
            else
            {
                return false;
            }
        }
        else
        {
            return "VIEW";
        }
    }
    
     if (str_contain($query, "DROP VIEW", 0, strlen('DROP VIEW')))
    {
        if ($db_required)
        {
            if (str_contain($query, "$db."))
            {
                return "VIEW";
            }
            else
            {
                return false;
            }
        }
        else
        {
            return "VIEW";
        }
    }
    
    $q = substr($query, 0, strpos($query, "BEGIN"));
    if ((
            str_contain($q, "CREATE", 0, strlen('CREATE')) 
            || str_contain($q, "ALTER", 0, strlen('ALTER'))
        ) 
            && str_contain($q, "PROCEDURE")
      )
    {
        if ($db_required)
        {
            if (str_contain($q, "$db."))
            {
                return "PROCEDURE";
            }
            else
            {
                return false;
            }
        }
        else
        {
            return "PROCEDURE";
        }
    }
    
    if (str_contain($query, "DROP PROCEDURE", 0, strlen('DROP PROCEDURE')))
    {
        if ($db_required)
        {
            if (str_contain($query, "$db."))
            {
                return "PROCEDURE";
            }
            else
            {
                return false;
            }
        }
        else
        {
            return "PROCEDURE";
        }
    }
    
    $q = substr($query, 0, strpos($query, "RETURNS"));
    if ((
            str_contain($q, "CREATE", 0, strlen('CREATE')) 
            || str_contain($q, "ALTER", 0, strlen('ALTER'))
        ) 
            && str_contain($q, "FUNCTION")
      )
    {
        if ($db_required)
        {
            if (str_contain($q, "$db."))
            {
                return "FUNCTION";
            }
            else
            {
                return false;
            }
        }
        else
        {
            return "FUNCTION";
        }
    }
    
    if (str_contain($query, "DROP FUNCTION", 0, strlen('DROP FUNCTION')))
    {
        if ($db_required)
        {
            if (str_contain($query, "$db."))
            {
                return "FUNCTION";
            }
            else
            {
                return false;
            }
        }
        else
        {
            return "FUNCTION";
        }
    }
    
    return false;
}

function get_DML_query_type($query, $table, $db, $db_required)
{
    $query = strtoupper($query);
    
    if (!$db_required)
    {
        $find = "INSERT INTO $table";    
        if (str_contain($query, $find, 0, strlen($find)))
        {
            return "INSERT";
        }
    }
    
    $find = "INSERT INTO $db.$table";
    if (str_contain($query, $find, 0, strlen($find)))
    {
        return "INSERT";
    }
    
    if (!$db_required)
    {
        $find = "UPDATE $table";    
        if (str_contain($query, $find, 0, strlen($find)))
        {
            return "UPDATE";
        }
    }
    
    $find = "UPDATE $db.$table";
    if (str_contain($query, $find, 0, strlen($find)))
    {
        return "UPDATE";
    }
    
    if (!$db_required)
    {
        $find = "DELETE FROM $table";    
        if (str_contain($query, $find, 0, strlen($find)))
        {
            return "DELETE";
        }
    }
    
    $find = "DELETE FROM $db.$table";
    if (str_contain($query, $find, 0, strlen($find)))
    {
        return "DELETE";
    }
    
    if (!$db_required)
    {
        $find = "TRUNCATE TABLE $table";    
        if (str_contain($query, $find, 0, strlen($find)))
        {
            return "TRUNCATE";
        }
    }
    
    $find = "TRUNCATE TABLE $db.$table";
    if (str_contain($query, $find, 0, strlen($find)))
    {
        return "TRUNCATE";
    }
    
    return false;
}

function git_version($path)
{
    $stringfromfile = file("$path/.git/HEAD");

    $firstLine = $stringfromfile[0]; //get the string from the array

    $explodedstring = explode("/", $firstLine, 3); //seperate out by the "/" in the string

    $branchname = $explodedstring[2]; //get the one that is always the branch name
    
    return strtolower(trim($branchname));
}

//$arg = "CREATE TABLE `test_schema`.`users` ( `id` INT UNSIGNED NOT NULL AUTO_INCREMENT, `email` VARCHAR(45) NOT NULL, `password` VARCHAR(45) NOT NULL, `created` DATETIME NOT NULL, PRIMARY KEY (`id`))";
//        
//debug([
//    $arg,
//    "CREATE TABLE" => [
//        str_contain($arg, "CREATE TABLE") ? "contain" : "not-contain",
//        "with database name" => str_contain($arg, "CREATE TABLE `" . config::$database['database'] . "`.") ? "contain" : "not-contain"
//    ],
//    "DROP PROCEDURE" => [
//        str_contain($arg, "DROP PROCEDURE") ? "contain" : "not-contain",
//        "with database name" => str_contain($arg, "DROP PROCEDURE `" . config::$database['database'] . "`.") ? "contain" : "not-contain"
//    ],
//    "CREATE OR REPLACE VIEW" => str_contain($arg, "CREATE OR REPLACE VIEW")  ? "contain" : "not-contain",
//    "CREATE PROCEDURE" => str_contain($arg, "CREATE PROCEDURE") ? "contain" : "not-contain"
//]);
//
//exit;