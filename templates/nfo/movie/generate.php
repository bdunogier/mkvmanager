<script type="text/javascript" src="/js/jquery-1.4.2.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {

    /**
     * SelectTrailer info update action
     */
    $("form.frmNfoAction input[type='button']").bind( 'click', function(){
        form = $(this).parent();
        actionType = $(this).attr( 'name' );
        actionValue = form.children( "input[name='actionValue']" ).val();
        $("#frmUpdateNfo").trigger( 'updateNfo', [ actionType, actionValue, form ] );
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
     * SelectMainPoster action apply callback
     */
    $("form.frmNfoPosterAction,").bind( 'applyAction', function( e, actionType, actionValue ) {
        console.log( 'frmNfoPosterAction.applyAction', [e, actionType, actionValue] );
        postersDiv = $('#divPosters');

        switch ( actionType )
        {
            case 'SelectMainPoster':
                // update selected poster's actionValue
                selectedPoster = $("#divPosters div.posterContainer:eq(" + actionValue + ")");
                selectedPoster.find("input[name='actionValue']").val(0);

                // update first poster's actionValue
                firstPoster = postersDiv.find("div.posterContainer:eq(0)");
                firstPoster.find("input[name='actionValue']").val(actionValue);

                // move top poster where selected one is
                if ( actionValue != 1 )
                {
                    previousPoster = selectedPoster.prev();
                    previousPoster.after( firstPoster );
                }

                // move selectedPoster to the top
                postersDiv.prepend( selectedPoster );
                break;

            case 'DisablePoster':
                // slice out everything from the disbled element to the end,
                overflow = $('#divPosters div.posterContainer')
                    .slice( actionValue )
                    // detach everything
                    .detach()
                    // slice the removed one out
                    .slice(1)
                    // decrease the index for all the detached elements before removing
                    .each( function(index,element){
                        actionValueElement = $(this).find("input[name='actionValue']");
                        actionValueElement.val( actionValueElement.val() - 1 );
                });
                postersDiv.append( overflow );
                break;

            default:
                alert('[frmNfoPosterAction.applyAction] Unknown action ' + actionType );
        }
    });

    /**
     * SelectMainFanart action apply callback
     */
    $("form.frmNfoFanartAction,").bind( 'applyAction', function( e, actionType, actionValue ) {
        console.log( 'frmNfoFanartAction.applyAction', [e, actionType, actionValue] );
        fanartsDiv = $('#divFanarts');

        switch ( actionType )
        {
            case 'SelectMainFanart':
                // update selected poster's actionValue
                selectedFanart = $("#divFanarts div.fanartContainer:eq(" + actionValue + ")");
                selectedFanart.find("input[name='actionValue']").val(0);

                // update first poster's actionValue
                firstFanart = fanartsDiv.find("div.fanartContainer:eq(0)");
                firstFanart.find("input[name='actionValue']").val(actionValue);

                // move top poster where selected one is
                if ( actionValue != 1 )
                {
                    previousFanart = selectedFanart.prev();
                    previousFanart.after( firstFanart );
                }

                // move selectedPoster to the top
                fanartsDiv.prepend( selectedFanart );
                break;

            case 'DisableFanart':
                // slice out everything from the disbled element to the end,
                overflow = $('#divFanarts div.fanartContainer')
                    .slice( actionValue )
                    // detach everything
                    .detach()
                    // slice the removed one out
                    .slice(1)
                    // decrease the index for all the detached elements before removing
                    .each( function(index,element){
                        actionValueElement = $(this).find("input[name='actionValue']");
                        actionValueElement.val( actionValueElement.val() - 1 );
                });
                fanartsDiv.append( overflow );
                break;

            default:
                alert('[frmNfoFanartAction.applyAction] Unknown action ' + actionType );
        }
    });

    /**
     * SelectMainPoster action apply callback
     */
    $("form.frmNfoFanartAction").bind( 'applyAction', function( e, actionType, actionValue ) {
        console.log( 'frmNfoPosterAction.applyAction', [e, actionType, actionValue] );

    });

    /**
     * Executes the nfo update action contained in actionForm
     *
     * Ex: $("#frmUpdateNfo").trigger( 'updateNfo', [ actionType, actionValue ] );
     *
     * @param string actionForm The form containing the action data
     * @param string successCallback The callback to call upon success
     */
    $("#frmUpdateNfo").bind( 'updateNfo', function( e, actionType, actionValue, actionForm ){
        // actionType = actionForm.children( "input[name='actionType']" ).val();
        // actionValue = actionForm.children( "input[name='actionValue']" ).val();

        $(this).children("input[name='actionType']").val( actionType );
        $(this).children("input[name='actionValue']").val( actionValue );

        // post the form using ajax, and instruct the caller to do its updates
        $.post(
            $(this).attr( 'action' ),
            $(this).serialize(),
            function( r ){
                 $("#frmUpdateNfo").children("input[name='info']" ).val( r.info );
                 $('#nfo').text( r.nfo );
                 actionForm.trigger( 'applyAction', [ actionType, actionValue ] );
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
        <td><form class="frmNfoAction frmNfoTrailerAction">
            <input type="button" name="SelectTrailer" value="Select this trailer" />
            <input type="hidden" name="actionValue" value="<?=$trailerIndex?>" />
        </form></td>
    </tr>
    <?endforeach?>
</table>

<h2>Posters</h2>
<div id="divPosters">
    <?foreach( $this->infos->posters as $posterIndex => $poster):?>
    <div class="posterContainer">
        <img src="<?=$poster->thumbnailUrl ?: $poster->fullUrl?>" width="92" />
        <form class="frmNfoAction frmNfoPosterAction">
            <input type="button" name="SelectMainPoster" value="Set as main" />
            <input type="button" name="DisablePoster" value="Disable" />
            <input type="hidden" name="actionValue" value="<?=$posterIndex?>" />
        </form>
    </div>
    <?endforeach?>
</div>

<h2>Fanarts</h2>
<div id="divFanarts">
    <?foreach( $this->infos->fanarts as $fanartIndex => $fanart):?>
    <div class="fanartContainer">
        <img src="<?=$fanart->thumbnailUrl ?: $fanart->fullUrl?>" width="200" />
        <form class="frmNfoAction frmNfoFanartAction">
            <input type="button" name="SelectMainFanart" value="Set as main" />
            <input type="button" name="DisableFanart" value="Disable" />
            <input type="hidden" name="actionValue" value="<?=$fanartIndex?>" />
        </form>
    </div>
    <?endforeach?>
</div>

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