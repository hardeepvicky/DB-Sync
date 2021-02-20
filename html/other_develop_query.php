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
            <a id="sync_now" class="btn btn-primary" href="javascript:void(0);">Sync Now</a>
        </div>
    </div>
</div>

<table class="table table-bordered table-sortable sr-databtable" id="query_table">
    <thead>
        <tr>
            <th style="width: 5%; text-align: center;" data-search-clear="1">
                #
            </th>
            <th data-search="1" data-sort="alpha">Developer</th>
            <th data-search="1">Query</th>         
            <th style="width: 150px" data-search="1" data-sort="alpha">Datetime</th>
            <th style="width: 80px">Actions</th>
            <th style="width: 20%;">Result</th>
        </tr>
    </thead>
    <tbody>
        <?php $a = 0; foreach($non_sync_data as $developer => $data): ?>
        <?php foreach($data as $arr):  $a++; ?>
            <tr>
                <td style="text-align: center;">
                    <?= $a ?>
                </td>
                <td><?= $developer ?></td>
                <td><?= $arr['query'] ?></td>
                <td><?= DateUtility::getDate($arr['datetime'], "d-M-Y H:i:s"); ?></td>
                <td>
                    <input type="hidden" class="developer" value="<?= $developer ?>" />
                    <input type="hidden" class="id" value="<?= $arr["id"] ?>" />
                    <select class="will_execute">
                        <option value="">Please Select</option>
                        <option value="1">Run</option>
                        <option value="0">Ignore</option>
                    </select>
                </td>
                <td class="result alert"></td>
            </tr>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </tbody>
</table>

<script type="text/javascript">
$(document).ready(function()
{
    $("#sync_now").click(function()
    {
        $("td.result").removeClass("alert-success alert-danger").html("");
        
        $("#query_table tbody tr").not(".sr-hidden").each(function()
        {
            var _tr = $(this);
            var data = {
                developer : _tr.find("input.developer").val(),
                id : _tr.find("input.id").val(),
                will_execute : _tr.find("select.will_execute").val(),
            };
            
            if (data.will_execute)
            {
                var url = '<?= Config::url("ajax_other_developer_query_sync") ?>';
                
                $.ajax({
                    url: url,
                    type: 'POST',
                    async: false, 
                    data : data,
                    success: function (response) 
                    {
                        if (response == "1")
                        {
                            _tr.find(".result").addClass("alert-success");
                            if (data.will_execute == "1")
                            {
                                _tr.find(".result").html("Run Successfully");
                            }
                            else
                            {
                                _tr.find(".result").html("Ignored");
                            }
                        }
                        else
                        {
                            _tr.find(".result").html(response).addClass("alert-danger");
                        }
                    }
                    error: function (jqXHR, exception) 
                    {
                        var msg = '';
                        if (jqXHR.status === 0) {
                            msg = 'Not connect.\n Verify Network.';
                        } else if (jqXHR.status == 404) {
                            msg = 'Requested page not found. [404]';
                        } else if (jqXHR.status == 500) {
                            msg = 'Internal Server Error [500].';
                        } else if (exception === 'parsererror') {
                            msg = 'Requested JSON parse failed.';
                        } else if (exception === 'timeout') {
                            msg = 'Time out error.';
                        } else if (exception === 'abort') {
                            msg = 'Ajax request aborted.';
                        } else {
                            msg = 'Uncaught Error.\n' + jqXHR.responseText;
                        }
                        
                        _tr.find(".result").html(msg).addClass("alert-danger");
                    }
                });
            }
        });
        
        return false;
    });
});
</script>