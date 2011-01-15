<style type="text/css">
</style>

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>

<script type="text/javascript">
mm.mergeQueue = {};
// 1 = html, 2 = objects list
mm.mergeQueue.mode = 2;
mm.mergeQueue.processing = false;
mm.mergeQueue.updateTable = function updateTable()
{
    if ( mm.mergeQueue.processing == true ) return;
    mm.mergeQueue.processing = true;
    $.get(
        '/ajax/merge-queue/<?=$this->items?>',
        function success( r ) {
            var htmlTable = '';
            if ( mm.mergeQueue.mode == 1 )
            {
                htmlTable = r.html_table;
            }
            else if ( mm.mergeQueue.mode == 2)
            {
                console.log( r );
                for ( var hash in r.operations ) {
                    operation =  r.operations[hash];

                    var progressText = '';
                    if ( operation.status == 'pending' )
                    {
                        progressText =
                            '<progress id="progressBar" value="' + operation.progress + '" max="100" />' +
                            '</progress><span class="percent">' + operation.progress + '%</span></td>';
                    }
                    else
                    {
                        progressText = 'N/A';
                    }

                    htmlTable +=
                        "<tr>" +
                        "<td>"+operation.hash+"</td>" +
                        "<td>"+operation.targetFileName+"</td>" +
                        "<td>"+operation.createTime+"</td>" +
                        "<td>"+operation.endTime+"</td>" +
                        "<td>"+progressText+"</td>" +
                        "</tr>";
                }
            }

            mm.mergeQueue.processing = false;
            $("#MergeQueueStatus").find("tr:gt(0)").remove();
            $("#MergeQueueStatus tr:last").after( htmlTable );
            // setTimeout( 'mm.mergeQueue.updateTable()', 200 );
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