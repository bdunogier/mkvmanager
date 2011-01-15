<style type="text/css">
</style>

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>

<script type="text/javascript">
mm.mergeQueue = {};
mm.mergeQueue.processing = false;
mm.mergeQueue.updateTable = function updateTable()
{
    if ( mm.mergeQueue.processing == true ) return;
    mm.mergeQueue.processing = true;
    $.get(
        '/ajax/merge-queue/<?=$this->items?>',
        function success( r ) {
            $("#MergeQueueStatus").find("tr:gt(0)").remove();
            $("#MergeQueueStatus tr:last").after( r.html_table );
            console.log( $("#MergeQueueStatus").find("tr:gt(0)") );
            mm.mergeQueue.processing = false;
            setTimeout( 'mm.mergeQueue.updateTable()', 200 );
        }, 'json' );
}
$(document).ready(function() {
    mm.mergeQueue.updateTable();
});
 </script>

<h1>Merge queue status</h1>

<table title="Merge queue status" id="MergeQueueStatus">
    <thead>
    <tr>
        <th>Hash</th>
        <th>File</th>
        <th>Created</th>
        <th>End time</th>
        <th>Progress</th>
    </tr>
    </thead>
    <tbody>
    </tbody>
</table>