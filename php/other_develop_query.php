<?php 
$sync_data = CsvUtility::fetchCSV(SYNC_FILE);
$last_sync = $last_sync_on = false;

if ($sync_data == false)
{
    $sync_data = array();
}
else
{
    $last_sync = end($sync_data);
    $last_sync_on = $last_sync['datetime'];
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
        
        $data = CsvUtility::fetchCSV(BASE_PATH . "developers/" . $file);
        $ret_data = array();
        foreach($data as $k => $arr)
        {
            $found = false;
            
            foreach($sync_data as $k => $sync_arr)
            {
                if ($developer == $sync_arr['developer'] && $arr['id'] == $sync_arr['ref_id'])
                {
                    $found = true;
                }
            }
            
            if ($found == FALSE && $arr["will_execute"] == 1)
            {
                $ret_data[$arr['id']] = $arr;
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
            if (!$mysql->query("SET GLOBAL general_log = 'OFF';"))
            {
                throw new Exception(mysqli_error(Mysql::$conn));
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
    
    $mysql->query("SET GLOBAL general_log = 'ON';");
    
    if ($handle)
    {
        fclose($handle);
        chmod(SYNC_FILE, 0777);  
    }
    
    header('Location:' . config::url("other_develop_query"));
    die();
}