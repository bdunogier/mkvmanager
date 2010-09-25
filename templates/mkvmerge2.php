<html>
<head>
        <title>MKV Merger</title>
        <style type="text/css">
            body {
                margin-left: 20%;
                margin-right: 20%;
            }

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
                width: 100%;
            }

            div.drive {
                float: left;
                text-align: center;
                padding: 5px 10px 5px 10px;
                margin: 5px;
            }

            div.drive img {
                display: inline;
            }
        </style>
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
        <script type="text/javascript">
        $(document).ready(function() {

            var SelectedDrive = false;
            var WinCmd = false;

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
                    console.log( data );
                    disk = data.RecommendedDisk;
                    if ( disk != "none" )
                    {
                        $(".drive").each( function( index )
                        {
                            if ( $(this).children('.DriveName')[0].value == disk )
                                $(this).click();
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
                    $("div#ConvertedCommand").html( data.Command );
                    // $("div#ConvertedFilesSubtitles").html( '<li>' +  );
                    // $("div#ConvertedCommand").html( data.Command );

                    // Subtitles
                    html = '<h2>Subtitles:</h2><ul>';
                    for ( file in data.SubtitleSourceFiles )
                        html += '<li>' + data.SubtitleSourceFiles[file].basename + '</li>';
                    html += '</ul>';
                    $("div#ConversionFilesSubtitles").html( html );

                    // Videos
                    html = '<h2>Videos:</h2><ul>';
                    for ( file in data.VideoSourceFiles )
                        html += '<li>' + data.VideoSourceFiles[file].basename + '</li>';
                    html += '</ul>';
                    $("div#ConversionFilesVideos").html( html );

                }, "json" );
            }

         });
         </script>
</head>
<body>
<h1>MKV Merger</h1>

<?php
$winCmd = isset( $_POST['WinCmd'] ) ? htmlentities( $_POST['WinCmd'] ) : '';
$formAction = str_replace( 'index.php/', '', $_SERVER['REQUEST_URI'] );
?>
<frameset>
    <legend>Convert windows CMD</legend>
    <form method="POST" action="<?=$formAction?>">
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
        <p><input type="checkbox" name="QueueCommand" value="1" id="chkQueueCommand" /><label for="chkQueueCommand">Add to queue</label></p>
        <p><input type="submit" name="ConvertWinCmd" /></p>
    </form>
</frameset>

<div id="ConvertedCommand" ></div>
<div id="ConversionFilesSubtitles" ></div>
<div id="ConversionFilesVideos" ></div>

</body>

