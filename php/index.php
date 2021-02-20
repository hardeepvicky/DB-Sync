<?php 
$csv_utility = new csv\CsvUtility(SYNC_FILE);
$csv_utility->setField("is_execute", csv\CsvDataType::BOOL);

$where = new csv\CsvWhere("is_execute", "", "1");

$sync_data = $csv_utility->find(array(), array($where));

if ($sync_data == false)
{
    $sync_data = array();
}