<div class="page-header">
    <h1>List of Executed Queries</h1>
</div>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>#</th>
            <th>Developer</th>            
            <th>Query</th>
            <th>Datetime</th>
        </tr>
    </thead>
    <tbody>
        <?php $a = 0; foreach($sync_data as $data): $a++; ?>
        <tr>
            <td><?= $a ?></td>
            <td><?= $data['developer'] ?></td>
            <td><?= $data['query'] ?></td>
            <td><?= DateUtility::getDate($data['datetime'], "d-M-Y H:i:s"); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<script type="text/javascript">
    $(document).ready(function() 
    {
        $('.table-bordered').DataTable({
            "order": [[ 0, "desc" ]],
            "pageLength": 50
        });
    });
</script>