<?php
function decodeSize( $bytes )
{
	$types = array( 'B', 'KO', 'MO', 'GO', 'TO' );
	for( $i = 0; $bytes >= 1024 && $i < ( count( $types ) -1 ); $bytes /= 1024, $i++ );
	return round( $bytes, 2 ) . " " . $types[$i];
}
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
			<?php
			// TODO: add smart sort. Best-fit first (that really is eye-candy...)
			// TODO: stupid, it requires filesize, and we don't have that...
			// biggest first it is then, excluding disks with less than 4.5 GB free
			// add a background color to the option based on the remaining space: orange for less than 4.5, white for more
			// stupid again.
			// TODO... We actually do, it's the source... but it requires AJAX, or some server side correction
			$dir = opendir( '/media/storage/' );
			foreach( new DirectoryIterator( '/media/storage/' ) as $disk )
			{
				$target = isset( $_POST['Target'] ) ? $_POST['Target'] : false;
				// var_dump( $disk );
				if ( $disk->isDot() )
					continue;
				$freespace = decodeSize( disk_free_space( $disk->getPathname() ) );
				$diskName = $disk->getFilename();
				$selectedText = ( $diskName == $target ) ? ' selected="selected"' : '';
				echo "<option value=\"{$diskName}\"{$selectedText}>{$diskName} ({$freespace} libres)</option>\n";
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

