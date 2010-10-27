<?php
?>
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

            span.filename {
                font-family: Andale Mono, monospace;
                font-size: 80%;
            }
        </style>
</head>
<body>
<h1>MKV Merger</h1>

<frameset>
    <legend>Convert windows CMD</legend>
    <form method="POST" action="<?php echo str_replace('index.php/', '', $_SERVER['REQUEST_URI'] ); ?>">
        <p>
            <textarea name="WinCmd" style="width:100%; height: 200px;"><?php
            if ( isset( $_POST['WinCmd'] ) )
                echo htmlentities( $_POST['WinCmd']);
            ?></textarea>
        </p>
        <p>
            <select name="Target">
            <option value="0">pick</option>
            <?php foreach( $this->targetDisks as $disk )
            {
                echo "<option value=\"{$disk->name}\"{$disk->selectedText}>{$disk->name} ({$disk->freespace} libres)</option>\n";
            }
            ?>
            </select>
            <p><input type="checkbox" name="QueueCommand" value="1" id="chkQueueCommand" /><label for="chkQueueCommand">Add to queue</label></p>
        </p>
        <p><input type="submit" name="ConvertWinCmd" /></p>
    </form>
    <?php

    if ( isset( $this->command ) ):?>
        <p>
        Titre: <?php echo $this->command->title; ?><br />
        Cible: <?php echo $this->command->target; ?><br />
        Sous titres: <ul>
        <?php foreach( $this->command->subtitleFiles as $file ) {
            echo "<li>{$file}</li>";
        }
        ?>
        </ul><br />
        Videos: <ul>
        <?php foreach( $this->command->videoFiles as $file ) {
            echo "<li>{$file}</li>";
        }
        ?>
        </ul>
        </p>
        <p style="font-family: monospace;"><?php echo $this->command; ?></p>
    <?php endif; ?>

</frameset>

</body>

