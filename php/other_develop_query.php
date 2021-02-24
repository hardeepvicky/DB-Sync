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

if (SYNC_LAST_DATETIME)
{
    if (DateUtility::compare(SYNC_LAST_DATETIME, $last_sync_on) > 0)
    {
        $last_sync_on = SYNC_LAST_DATETIME;
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
            
            if (SYNC_LAST_DATETIME)
            {
                if ( DateUtility::compare($developer_arr['datetime'], SYNC_LAST_DATETIME) <= 0 )
                {
                    $found = true;
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