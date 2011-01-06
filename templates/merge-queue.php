<style type="text/css">
</style>

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>

<script type="text/javascript">
var processing = false;
function updateTable()
{
    if ( processing == true ) return;
    processing = true;
    $.get(
        '/ajax/merge-queue-status',
        function success( r ) {
            $("#MergeQueueStatus").find("tr:gt(0)").remove();
            $("#MergeQueueStatus tr:last").after( r.html_table );
            console.log( $("#MergeQueueStatus").find("tr:gt(0)") );
            processing = false;
            setTimeout( 'updateTable()', 200 );
        }, 'json' );
}
$(document).ready(function() {
    updateTable();
});
 </script>

<h1>Merge queue status</h1>

<table title="Merge queue status" id="MergeQueueStatus">
    <tr>
        <th>Hash</th>
        <th>File</th>
        <th>Created</th>
        <th>End time</th>
        <th>Progress</th>
    </tr>
</table>