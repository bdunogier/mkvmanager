<style type="text/css">
button {
    font-size: 75%;
}
</style>

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
<script type="text/javascript" src="/js/date.format.js"></script>

<?php /*<script type="text/javascript">
mm.mergeQueue = {};
// 1 = html, 2 = objects list
mm.mergeQueue.mode = 2;
mm.mergeQueue.processing = false;
mm.mergeQueue.updateTable = function updateTable()
{
    var status_working = 2;

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
                    if ( operation.status == status_working )
                    {
                        progressText =
                            '<progress id="progressBar" value="' + operation.progress + '" max="100" />' +
                            '</progress><span class="percent">' + operation.progress + '%</span></td>';
                    }
                    else
                    {
                        progressText = 'N/A';
                    }

                    var createTime = new Date( operation.createTime*1000 );
                    var endTime = new Date( operation.endTime*1000 );
                    htmlTable +=
                        "<tr>" +
                        "<td>"+operation.hash+"</td>" +
                        "<td>"+operation.targetFileName+"</td>" +
                        "<td>"+createTime.format('dd/mm hh:MM')+"</td>" +
                        "<td>"+endTime.format('dd/mm hh:MM')+"</td>" +
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
*/?>
<script type="text/javascript">
$(document).ready( function() {
    $(".btnOperationArchive").click( function() {
        button = $(this);
        var hash = button.parent().parent().find('td:first').html();
        var url = '/ajax/sourcefiles/archive/' + hash;
        $.get( url, function success( r ) {
            console.log('r: ', r);
            console.log( 'r.status: ', r.status );
            if ( r.status == 'ok' )
            {
                button.parent().html('N/A');
            }
            else
            {
                if ( r.message == 'already_archived' )
                {
                    button.val( 'Already archived' );
                    button.attr( 'disabled', 'true' );
                }
                else
                {
                    button.parent().html('<span class="error">'+r.message+'</span>');
                }
            }
        }, "json" );
    });
});
</script>
<h1>Merge queue status</h1>

<? if ( !count( $this->operations )  ): ?>
<p>Empty list</p>
<? else: ?>
<table title="Merge queue status" id="MergeQueueStatus">
    <thead>
    <tr>
        <th>Hash</th>
        <th>File</th>
        <th>Created</th>
        <th>End time</th>
        <th>Progress</th>
        <th>Action !</th>
    </tr>
    </thead>
    <tbody>
        <!-- TEST
        <tr>
            <td>a57192815d00443ab95c5e7c00fc271e8446bd74</td>
            <td>The Big Bang Theory - 4x10 - The Alien Parasite Hypothesis.mkv</td>
            <td>06/01, 23:11:25</td>
            <td>07/01, 11:11:28</td>
            <td>N/A</td>
            <td><input type="button" class="btnOperationArchive" value="archive" /></td>
        </tr>
        END TEST -->
        <?php foreach( $this->operations as $operation ): ?>
        <tr>
            <td><?=$operation->hash?></td>
            <td><?=$operation->targetFileName?></td>
            <td><?=strftime( '%d/%m, %H:%I:%S', $operation->createTime )?></td>
            <td><?=strftime( '%d/%m, %H:%I:%S', $operation->endTime )?></td>
            <?php if ( $operation->status == mmMergeOperation::STATUS_RUNNING ): ?>
                <td><progress value="<?=$operation->progress?>" max="100" /></td>
            <?php else: ?>
                <td>N/A</td>
            <?php endif ?>

            <?php if ( $operation->sourceFileExists ): ?>
                <td><input type="button" class="btnOperationArchive" value="archive" /></td>
            <?php else: ?>
                <td>N/A</td>
            <?php endif ?>
        </tr>
        <? endforeach ?>
    </tbody>
</table>
<?endif?>