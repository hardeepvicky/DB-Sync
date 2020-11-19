<?php
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 60 * 60 * 30);   
            
require_once './php/include/functions.php';
require_once './shared_config.php';
require_once './php/include/GitUtility.php';
require_once './config.php';
require_once './php/include/DateUtility.php';
require_once './php/include/CsvUtility.php';
require_once './php/include/FileUtility.php';
require_once './php/include/Mysql.php';
require_once './php/include/Session.php';


if (!FileUtility::createFolder(BASE_PATH . "developers/"))
{
	die("failed to create path " . BASE_PATH . "developers/");
}

chmod(BASE_PATH, 0777);

$mysql = new Mysql(config::$database['server'], config::$database['username'], config::$database['password'], config::$database['database']);

if (empty(Mysql::$conn))
{
    die("Missing Mysql Connection");
}

$load_file = isset($_GET['load_file']) ? $_GET['load_file'] : "index";
$load_file .= ".php";

require_once "php/$load_file";

if (SQL_LOCAL_CHANGE_ENABLE)
{
    $mysql->query("SET GLOBAL log_output = 'TABLE';SET GLOBAL general_log = 'ON';");
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>DB Sync - <?= DEVELOPER ?></title>

        <link rel="stylesheet" type="text/css" href="html/bootstrap/css/bootstrap.min.css"/>
        <link rel="stylesheet" type="text/css" href="html/font-awesome/css/font-awesome.min.css"  />
        <link rel="stylesheet" type="text/css" href="html/bootstrap/css/bootstrap-theme.min.css"/>
        <link rel="stylesheet" type="text/css" href="html/bootstrap-dialog/bootstrap-dialog.min.css"/>
        <link rel="stylesheet" type="text/css" href="html/SRDatatable/style.css"/>
        <link rel="stylesheet" type="text/css" href="html/css/theme.css"/>
        
        
        <script src="html/js/jquery-3.1.1.js" type="text/javascript"></script>
        <script src="html/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>        
        <script src="html/bootstrap-dialog/bootstrap-dialog.min.js" type="text/javascript"></script>
        <script src="html/SRDatatable/script.js?1" type="text/javascript"></script>
        <script src="html/js/jquery-extend.js?17" type="text/javascript"></script>
        <script src="html/sortable/jquery.sortable.min.js" type="text/javascript"></script>
        
        <style>
            .page-header
            {
                margin: 0px;
            }
        </style>
    </head>
    <body>
        <nav class="navbar navbar-inverse navbar-fixed-top">
            <div class="container">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="<?= BASE_URL; ?>">Db Sync - <?= DEVELOPER; ?></a>
                </div>
                <div id="navbar" class="navbar-collapse collapse">
                    <ul class="nav navbar-nav">
                        <li class="<?= $load_file == "index.php" ? "active" : "" ?>"><a href="<?= config::url("index") ?>">Home</a></li>
                        <li class="<?= $load_file == "fetch_query.php" ? "active" : "" ?>"><a href="<?= config::url("fetch_query") ?>">Local Database Changes</a></li>
                        <li class="<?= $load_file == "other_develop_query.php" ? "active" : "" ?>"><a href="<?= config::url("other_develop_query") ?>">Non Sync</a></li>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="container theme-showcase" role="main">
            
            <?php if (Session::hasFlash('success')): ?>
                <div class="alert alert-success" role="alert">
                    <strong>Done!</strong> <?= Session::readFlash('success'); ?>
                </div>
            <?php endif; ?>
            
            <?php if (Session::hasFlash('warning')): ?>
                <div class="alert alert-warning" role="alert">
                    <strong>Warning!</strong> <?= Session::readFlash('warning'); ?>
                </div>
            <?php endif; ?>
            
            <?php if (Session::hasFlash('info')): ?>
                <div class="alert alert-warning" role="alert">
                    <strong>Info!</strong> <?= Session::readFlash('info'); ?>
                </div>
            <?php endif; ?>
            
            <?php if (Session::hasFlash('failure')): ?>
                <div class="alert alert-danger" role="alert">
                    <strong>Failure!</strong> <?= Session::readFlash('failure'); ?>
                </div>
            <?php endif; ?>
            
			<h5>Database : <?php echo config::$database['database']  ?></h5>
            <?php include_once "html/$load_file"; ?>
        </div>
    </body>
    <script type="text/javascript">
        $(document).ready(function ()
        {
            $(".sr-databtable").srDatatable();
        })
    </script>
</html>
