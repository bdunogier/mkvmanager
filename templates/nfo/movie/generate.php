<script type="text/javascript" src="/js/jquery-1.4.2.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {

    /**
     * SelectTrailer info update action
     */
    $("form.frmNfoTrailerAction input[type='button']").bind( 'click', function(){
        form = $(this).parent();
        $("#frmUpdateNfo").trigger( 'updateNfo', [ form, form ] );
    });

    /**
     * SelectTrailer action apply callback
     */
    $("form.frmNfoTrailerAction").bind( 'applyAction', function( e, actionType, actionValue ){
        // set original first trailer id to the moved one
        affectedTrailer = $('#tblTrailers tr:eq(0)');
        affectedTrailer.find("input[name='actionValue']").val( actionValue );

        // set selected trailer id to 0
        selectedTrailer = $('#tblTrailers tr:eq(' + actionValue + ')');
        selectedTrailer.find("input[name='actionValue']").val( 0 );

        // swap trailers
        $('#tblTrailers').prepend( selectedTrailer );
    });


    /**
     * Executes the nfo update action actionType with the value actionValue
     *
     * Ex: $("#frmUpdateNfo").trigger( 'updateNfo', [ actionType, actionValue ] );
     *
     * @param string actionForm The form containing the action data
     * @param string successCallback The callback to call upon success
     */
    $("#frmUpdateNfo").bind( 'updateNfo', function( e, actionForm, callbackItem ){
        actionType = actionForm.children( "input[name='actionType']" ).val();
        actionValue = actionForm.children( "input[name='actionValue']" ).val();

        $(this).children("input[name='actionType']").val( actionType );
        $(this).children("input[name='actionValue']").val( actionValue );

        // post the form using ajax, and apply the changes to the caller
        $.post(
            $(this).attr( 'action' ),
            $(this).serialize(),
            function( r ){
                 $("#frmUpdateNfo").children("input[name='info']" ).val( r.info );
                 $('#nfo').text( r.nfo );
                 callbackItem.trigger( 'applyAction', [ actionType, actionValue ] );
            }, "json"
        );
    });
});
</script>
<h1><?=$this->infos->originalTitle?></h1>

<h2>Trailers</h2>
<table id="tblTrailers">
    <?foreach( $this->infos->trailers as $trailerIndex => $trailer):?>
    <tr>
        <td><a href="<?=(string)$trailer?>"><?=(string)$trailer?></a></td>
        <td><?=$trailer->title?></td>
        <td><form class="frmNfoTrailerAction" method="get">
            <input type="button" name="actionType" value="SelectTrailer" />
            <input type="hidden" name="actionValue" value="<?=$trailerIndex?>" />
        </form></td>
    </tr>
    <?endforeach?>
</table>

<h2>Posters</h2>
<h2>Fanarts</h2>

<h2>NFO</h2>
<pre id="nfo"><?=htmlentities( utf8_decode( $this->nfo ) )?></pre>

<!-- AJAX nfo update form-->
<form id="frmUpdateNfo" method="post" action="<?=$this->updateUrl?>">
    <input type="hidden" name="info" value="<?=htmlentities( utf8_decode( var_export( $this->infos, true ) ) )?>">
    <input type="hidden" name="actionType">
    <input type="hidden" name="actionValue">
</form>

<!-- nfo save form -->
<form method="post" action="<?=$this->saveUrl?>">
    <input type="submit" value="Save NFO" />
</form>
</h1>