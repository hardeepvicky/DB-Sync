<form method="POST" action="<?= config::url("fetch_query", array("write_query_to_csv" => 1)) ?>">
    
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
            <button class="btn btn-primary submit">Write Changes To Git <b><?= GIT_VERSION ?></b> Branch</button>
        </div>
    </div>
</div>


<table class="table table-bordered">
    <thead>
        <tr>
            <th><input type="checkbox"  class="chk-select-all" data-href=".chk-child"></th>
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
            <td><input type="checkbox" class="chk-child" name="data[ids][]" value="<?= $log["id"] ?>" /></td>
            <td><?= $a ?></td>
            <td><?= $log['query'] ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
    
<script type="text/javascript">
    $(".submit").click(function()
    {
        if ($("input.chk-child:checked").length == 0)
        {
            BootstrapDialog.show({
                type: BootstrapDialog.TYPE_DANGER,
                title: 'Error',
                message: 'Please Select at least one checkbox',
            });
            
            return false;
        }
        
        $(this).parents("form").trigger("submit");
        return false;
    });
    
    $(document).ready(function()
    {
        $(".chk-select-all").chkSelectAll();
    });
</script>