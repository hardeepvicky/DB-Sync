<div class="page-header">
    <h1>List of Executed Queries</h1>
</div>
<table class="table table-bordered sr-databtable">
    <thead>
        <tr>
            <th data-search-clear="1" style="width: 10%">#</th>
            <th data-search="1" data-sort="alpha">Developer</th>            
            <th data-search="1">Query</th>
            <th style="width: 150px;" data-search="1" data-sort="alpha">Datetime</th>
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