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
                padding: 10px;
            }

            div.drive img {
                display: inline;
            }
        </style>
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
        <script type="text/javascript">
        $(document).ready(function() {

            // On command change
            $("#FormWinCmd").blur(function() {
                // Send command to server
                // Or trigger hard drive selection message ?
                $.post( "/ajax/mkvmerge", { WinCmd: this.value, Target: "CARROT" },
                function success( data ) {
                    console.log( data.command );
                    $("div#ConvertedCommand").html( data.command.command.command );
                }, "json" );
            });

            // On disk icon click
            $(".drive").click(function() {
                $(this).css( "background-color", "green" );
            });

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
            </div>
        <?php endforeach ?>
        </div>
        <div style="clear: both"></div>
        <p><input type="checkbox" name="QueueCommand" value="1" id="chkQueueCommand" /><label for="chkQueueCommand">Add to queue</label></p>
        <p><input type="submit" name="ConvertWinCmd" /></p>
    </form>
</frameset>

<div id="ConvertedCommand" ></div>

</body>

