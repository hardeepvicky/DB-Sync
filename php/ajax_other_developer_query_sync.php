<?php
$file = BASE_PATH . "developers/" . $_POST['developer'] . ".csv";
        
$developer_utility = new csv\CsvUtility($file);
$developer_utility->setField("id", csv\CsvDataType::NUMBER);

$where = new csv\CsvWhere("id", "=", $_POST['id']);
$developer_data = $developer_utility->find([], [$where]);

try
{
    if (empty($developer_data))
    {
        throw new Exception("Invalid id : " . $_POST['id'] . " in " . $_POST['developer'] . ".csv" );
    }

    $developer_data = reset($developer_data);

    if ($_POST['will_execute'])
    {
        $db = config::$database['database'];
        
        if (SQL_LOCAL_CHANGE_ENABLE && !$mysql->query("SET GLOBAL general_log = 'OFF';"))
        {
            throw new Exception(mysqli_error(Mysql::$conn));
        }
        
        if (!$mysql->query("USE $db;"))
        {
            throw new Exception(mysqli_error(Mysql::$conn));
        }


        if($mysql->query($developer_data["query"]) == false)
        {
            throw new Exception(mysqli_error(Mysql::$conn));
        }
    }

    $csv_data[] = array(
        "ref_id" => $_POST['id'],
        "developer" => $_POST['developer'],
        "datetime" => $developer_data["datetime"],
        "query" => $developer_data["query"],
        "is_execute" => $_POST['will_execute']
    );

    $sync_utility = new csv\CsvUtility(SYNC_FILE);
    $sync_utility->write($csv_data, "a");
}
catch(Exception $ex)
{
    echo $ex->getMessage();
}

if (SQL_LOCAL_CHANGE_ENABLE && !$mysql->query("SET GLOBAL general_log = 'ON';"))
{
    die(mysqli_error(Mysql::$conn));
}

echo 1; exit;
