<style>
    .mover
    {
        padding : 5px;
    }
    
    .mover:hover
    {
        cursor: pointer;
    }
</style>
<form method="POST" action="<?= config::url("other_develop_query", array("sync_now" => 1)) ?>">
<div class="page-header">
    <div class="row">
        <div class="col-12 col-md-6 pull-left">
            <h1>Non Sync Queries</h1>
            <h4>Last Sync Datetime - <?= $last_sync_on ?  DateUtility::getDate($last_sync_on, 'd-M-Y H:i:s') : ""; ?></h4>
            <p>
                List of Queriers made by other developer.<br/> <b>Note : </b> Make Sure you have pull the latest change from git
            </p>
        </div>
        <div class="col-12 col-md-6 pull-right" style="text-align: right; margin-top: 30px;">            
            <a class="btn btn-primary submit" href="#">Sync Now</a>
        </div>
    </div>
</div>

<table class="table table-bordered table-sortable">
    <thead>
        <tr>
            <th><input type="checkbox"  class="chk-select-all" data-href=".chk-child"> #</th>
            <th>Developer</th>         
            <th>Query</th>         
            <th>Datetime</th>
        </tr>
    </thead>
    <tbody>
        <?php $a = 0; foreach($non_sync_data as $developer => $data): ?>
        <?php foreach($data as $arr):  $a++; ?>
            <tr>
                <td>
                    <span class="glyphicon glyphicon-move mover"></span>
                    <input type="hidden" class="chk-child" name="data[<?= $developer ?>][<?= $arr['id']; ?>]" value="0" />
                    <input type="checkbox" class="chk-child" name="data[<?= $developer ?>][<?= $arr['id']; ?>]" value="1" />
                    <?= $a ?>
                </td>
                <td><?= $developer ?></td>
                <td><?= $arr['query'] ?></td>
                <td><?= DateUtility::getDate($arr['datetime'], "d-M-Y H:i:s"); ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </tbody>
</table>
</form>
<script type="text/javascript">
    $("a.submit").click(function()
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
        $('.table-sortable tbody').sortable({
            handle: 'span.mover'
        });

        $(".chk-select-all").chkSelectAll();
    });
</script>