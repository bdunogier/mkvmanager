<style type="text/css">
    p.error {
        color: red;
    }

    div#ConvertedCommand {
        font-family: Andale Mono, monospace;
    }

    span.filename {
        font-family: Andale Mono, monospace;
        font-size: 80%;
    }

    div.drivesList {
        font-family: Andale Mono, monospace;
        text-align: center;
    }

    div.drive {
        float: left;
        text-align: center;
        padding: 5px 10px 5px 10px;
        margin: 3px;
        border: 2px solid white;
    }

    div.drive img {
        display: inline;
    }

    div.drive.recommendedDisk {
        border: 2px dotted;
    }

    div.drive.recommendDiskWithSpace {
        border-color: green;
    }

    div.drive.recommendDiskWithoutSpace {
        border-color: red;
    }
</style>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {

    var SelectedDrive = false;
    var WinCmd = false;
    var ConvertedCmd = false;

    // Add converted merge to the queue
    $("#BtnQueueOperation").click( function() {
        $.post( "/ajax/queue-command", { MergeCommand: ConvertedCmd },
        function success( data ) {
            $("#BtnQueueOperation").val( "Done. Status: " + data.status );
        }, "json" );
    });

    // On command change
    $("#FormWinCmd").blur(function() {
        value = $("#FormWinCmd")[0].value;
        if ( value != '' ) WinCmd = value;
        bestFit();
        convertCommand();
    });

    // On disk icon click
    $(".drive").click(function() {
        // reset all
        $(".drive").each( function( index ) {
            $(this).css( "background-color", "" );
        });

        // change color for clicked
        $(this).css( "background-color", "#33cc33" );
        SelectedDrive = $(this).children('.DriveName')[0].value;

        convertCommand();
    });

    function bestFit()
    {
        if ( WinCmd == false ) return;

        // Send command to server
        // Or trigger hard drive selection message ?
        $.post( "/ajax/bestfit", { WinCmd: WinCmd },
        function success( data ) {
            disk = data.RecommendedDisk;
            if ( disk != "none" )
            {
                $(".drive").each( function( index )
                {
                    if ( $(this).children('.DriveName')[0].value == disk )
                    {
                        $(this).addClass( 'RecommendedDisk' );
                        if ( data.RecommendedDiskHasFreeSpace == 'true' )
                            $(this).addClass( 'recommendDiskWithSpace' );
                        else
                            $(this).addClass( 'recommendDiskWithoutSpace' );
                    }
                    else
                    {
                        if ( $(this).hasClass( 'RecommendedDisk' ) )
                            $(this).removeClass( 'RecommendedDisk' );
                        if ( $(this).hasClass( 'recommendDiskWithSpace' ) )
                            $(this).removeClass( 'recommendDiskWithSpace' );
                        if ( $(this).hasClass( 'recommendDiskWithoutSpace' ) )
                            $(this).removeClass( 'recommendDiskWithoutSpace' );
                    }
                });
            }
        }, "json" );
    }

    function convertCommand()
    {
        if ( SelectedDrive == false || WinCmd == false ) return;

        // Send command to server
        // Or trigger hard drive selection message ?
        $.post( "/ajax/mkvmerge", { WinCmd: WinCmd, Target: SelectedDrive },
        function success( data ) {
            ConvertedCmd = data.Command;
            $("code#ConvertedCommand").html( ConvertedCmd );
            $("#FrmHiddenMergeCommand").val( ConvertedCmd );

            // Subtitles
            html = '<h2>Subtitles:</h2><ul>';
            for ( file in data.SubtitleFiles )
                html += '<li>' + data.SubtitleFiles[file].basename + '</li>';
            html += '</ul>';
            $("div#ConversionFilesSubtitles").html( html );

            // Videos
            html = '<h2>Videos:</h2><ul>';
            for ( file in data.VideoFiles )
                html += '<li>' + data.VideoFiles[file].basename + '</li>';
            html += '</ul>';
            $("div#ConversionFilesVideos").html( html );

        }, "json" );
    }

 });
 </script>

<h1>MKV Merger</h1>

<?php
$winCmd = isset( $_POST['WinCmd'] ) ? htmlentities( $_POST['WinCmd'] ) : '';
?>

<textarea name="WinCmd" id="FormWinCmd" style="width:100%; height: 200px;"><?=$winCmd?></textarea>
<div class="drivesList">
<?php foreach( $this->targetDisks as $disk ) : ?>
    <div class="drive">
        <div class="name"><?=$disk->name?></div>
        <img src="/images/icons/harddrive.png" width="64" heigh="64" title="Disk: <?=$disk->name?>" />
        <div class="freespace"><?=$disk->freespace?></div>
        <input type="hidden" class="DriveName" value="<?=$disk->name?>" />
    </div>
<?php endforeach ?>
</div>
<div style="clear: both"></div>
<input id="ConvertTarget" type="hidden" name="Target" value="-1" />

<blockquote><code id="ConvertedCommand"></code></blockquote>
<div id="ActionBar"><input type="button" id="BtnQueueOperation" value="Queue merge operation" /></div>
<div id="ConversionFilesSubtitles" ></div>
<div id="ConversionFilesVideos" ></div>
