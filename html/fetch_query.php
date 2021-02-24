<style>
    .query_up, .query_down
    {
        background-color: #EEE;
        border : 1px solid #CCC;
        border-radius: 50%;
        cursor: pointer;
    }
    .divider{
        width : 100%;
        margin: 5px 0;
    }
</style>
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
            <button class="btn btn-primary submit">Write Changes to <b><?= DEVELOPER ?>.csv</b></button>
        </div>
    </div>
</div>


<table class="table table-bordered sr-databtable">
    <thead>
        <tr>
            <th><input type="checkbox"  class="chk-select-all" data-href=".chk-child"></th>
            <th data-search-clear="1" style="width: 8%; text-align: center;">#</th>
            <th data-search="1">Query</th>         
            <th data-search="1" style="width: 170px; text-align: center;">DateTime</th>
        </tr>
    </thead>
    <tbody>
        <?php $a = 0; 
            foreach($logs as $k => $log): 
                $a++; 
        ?>
        <tr>
            <td>
                <input type="hidden" name="data[<?= $k ?>][id]" value="<?= $log['id'] ?>" />
                <input type="hidden" name="data[<?= $k ?>][query]" value="<?= $log['query'] ?>" />
                <input type="hidden" name="data[<?= $k ?>][datetime]" value="<?= $log['datetime'] ?>" />
                <input type="checkbox" class="chk-child" name="data[<?= $k ?>][will_execute]" value="1" />
            </td>
            <td style="text-align: center;">
                <div class="row">
                    <div class="col-md-6">
                        <div class="query_up"><i class="fa fa-arrow-up"></i></div>
                        <div class="divider"><?= $a ?></div>
                        <div class="query_down"><i class="fa fa-arrow-down"></i></div>
                    </div>                    
                </div>
                
            </td>
            <td><?= $log['query'] ?></td>
            <td style="text-align: center;"><?= $log['datetime'] ?></td>
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
        
        $(".query_up").click(function()
        {
            $(this).closest("table").find("td").css("border", "");
            
            var _tr = $(this).closest("tr");   
            
            _tr.find("td").css("border", "1px solid #337ab7");
            
            _tr.prev().insertAfter(_tr);
        });
        
        $(".query_down").click(function()
        {
            $(this).closest("table").find("td").css("border", "");
            
            var _tr = $(this).closest("tr");
            _tr.insertAfter( _tr.next() );
            
            _tr.find("td").css("border", "1px solid #337ab7");
        });
    });
</script>