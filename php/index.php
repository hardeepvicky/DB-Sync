<?php 
$file = new CsvUtility(SYNC_FILE);
$sync_data = $file->find(array(), array("is_execute" => 1));

if ($sync_data == false)
{
    $sync_data = array();
}