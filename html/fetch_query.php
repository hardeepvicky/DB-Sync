<div class="page-header">
    <div class="row">
        <div class="col-12 col-md-6 pull-left">
            <h1>Local Database Changes</h1>
            <h4>Last Write Datetime - <?= $last_sync_on ?  DateUtility::getDate($last_sync_on, 'd-M-Y H:i:s') : ""; ?></h4>
            <p>
                List of Changes done by you at your local database
            </p>
        </div>
        <div class="col-12 col-md-6 pull-right" style="text-align: right; margin-top: 30px;">            
            <a href="<?= config::url("fetch_query", array("write_query_to_csv" => "1")) ?>" class="btn btn-primary">Write Changes To Git <b><?= GIT_VERSION ?></b> Branch</a>
        </div>
    </div>
</div>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>#</th>
            <th>Query</th>         
        </tr>
    </thead>
    <tbody>
        <?php $a = 0; 
            foreach($logs as $k => $log): 
                $a++; 
        ?>
        <tr>
            <td><?= $a ?></td>
            <td><?= $log['query'] ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>