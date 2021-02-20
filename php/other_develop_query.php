<?php 
$sync_utility = new csv\CsvUtility(SYNC_FILE);
$sync_utility->setField("datetime", csv\CsvDataType::DATETIME);

$temp = $sync_utility->find();

$last_sync_log = [];
$last_sync_on = false;

foreach($temp as $arr)
{
    $last_sync_log[$arr['developer']][] = $arr;
    
    if ($last_sync_on)
    {
        if (DateUtility::compare($arr['datetime'], $last_sync_on) > 0)
        {
            $last_sync_on = $arr['datetime'];
        }
    }
    else
    {
        $last_sync_on = $arr['datetime'];
    }
}

$non_sync_data = array();

$developer_files = FileUtility::getFileList(BASE_PATH . "developers/", array("csv"));

foreach($developer_files as $k => $file)
{
    if ($file == DEVELOPER . ".csv")
    {
        unset($developer_files[$k]);
    }
    else
    {
        $developer = pathinfo(BASE_PATH . "developers/" . $file, PATHINFO_FILENAME);
        
        $developer_utility = new csv\CsvUtility(BASE_PATH . "developers/" . $file);
        $developer_data = $developer_utility->find();
        
        $ret_data = array();
        foreach($developer_data as $k => $developer_arr)
        {
            $found = false;
            
            if ( isset($last_sync_log[$developer]) )
            {
                foreach($last_sync_log[$developer] as $last_sync_arr)
                {
                    if ($last_sync_arr["ref_id"] == $developer_arr["id"])
                    {
                        $found = true;
                    }
                }
            }
            
            if ($found == FALSE && $developer_arr["will_execute"] == 1)
            {
                $ret_data[$developer_arr['id']] = $developer_arr;
            }
        }
        
        $non_sync_data[$developer] = $ret_data;
    }
}

if (isset($_GET['sync_now']))
{
    $post_data = $_POST['data'];
    //debug($post_data); exit;
    $print_header = file_exists(SYNC_FILE) ? false : true;
    //debug($print_header); exit;
    $handle = fopen(SYNC_FILE, "a");
    try
    {
        $delimeter = ",";
        
        if ($handle) 
        {
            if ($print_header)
            {
                $headers = array("id", "ref_id", "developer", "datetime", "query", "is_execute");
                
                fputcsv($handle, $headers, $delimeter);
            }
            
            $db = config::$database['database'];
            
            if (SQL_LOCAL_CHANGE_ENABLE)
            {
                if (!$mysql->query("SET GLOBAL general_log = 'OFF';"))
                {
                    throw new Exception(mysqli_error(Mysql::$conn));
                }
            }
            
            if (!$mysql->query("USE $db;"))
            {
                throw new Exception(mysqli_error(Mysql::$conn));
            }
            
            $auto_id = $last_sync ? $last_sync['id'] + 1 : 1;
            foreach($post_data as $developer => $id_list)
            {
                foreach($id_list as $id => $will_execute)
                {
                    $arr['query'] .= ";";
                    $arr = $non_sync_data[$developer][$id];
                
                    if($will_execute && $mysql->query($arr['query']) == false)
                    {
                        throw new Exception(mysqli_error(Mysql::$conn));
                    }

                    $csv_data = array(
                        "id" => $auto_id,
                        "ref_id" => $id,
                        "developer" => $developer,
                        "datetime" => $arr['datetime'],
                        "query" => $arr['query'],
                        "is_execute" => $will_execute
                    );
                    
                    fputcsv($handle, $csv_data, $delimeter);
                    $auto_id++;
                }
            }
        }
        else
        {
            throw new Exception("Failed to open file " . SYNC_FILE);            
        }
        
        Session::writeFlash("success", "Queries successfully executed.");
    }  
    catch (Exception $ex)
    {
        Session::writeFlash("failure", $ex->getMessage());
    }
    
    if (SQL_LOCAL_CHANGE_ENABLE)
    {
        $mysql->query("SET GLOBAL general_log = 'ON';");
    }
    
    if ($handle)
    {
        fclose($handle);
        chmod(SYNC_FILE, 0777);  
    }
    
    header('Location:' . config::url("other_develop_query"));
    die();
}