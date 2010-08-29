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
	<legend>Status</legend>
	<?php
	/*$db = new SQLite3( "tmp/mergequeue.db" );
	$statusList = $db
		->query( "SELECT * FROM `commands` WHERE `pid` = 0" )
		->fetchArray( SQLITE3_ASSOC );
	if ( $statusList === false )
	{
		echo "<p>Empty queue</p>";
	}
	else
	{
		echo "<pre>"; var_dump( $array ); echo "</pre>";
	}*/
	?>
</frameset>

<frameset>
	<legend>Convert windows CMD</legend>
	<form method="POST" action="<?php echo $_SERVER['PHP_SELF'] ?>">
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

	if ( isset( $_POST['ConvertWinCmd'] ) )
	{
		if ( isset( $_POST['Target'] ) && ( $_POST['Target'] == "0") )
		{
			die( '<p style="color: red">Target is mandatory</p>' );
		}

		try {
			$command = MKVMergeCommandImportWindowsGUI::convert( $_POST['WinCmd'], $_POST['Target'] );
		} catch ( Exception $e ) {
			$exceptionMessage = $e->getMessage();
			$callstack = $e->getTraceAsString();
			$message = <<<EOF
<p class="error">An exception has occured in <span class="filename">{$e->getFile()}:{$e->getLine()}</p>
<p class="error message">{$exceptionMessage}</p>
<pre class="error dump">{$callstack}</pre>
EOF;
			die( $message );
		}

		// symlink
		$command->appendSymLink = true;
		$command->appendMessage = true;
	} // end if ( isset( $_POST['ConvertWinCmd'] )

	if ( isset( $command ) ):?>
		<p>
		Titre: <?php echo $command->title; ?><br />
		Cible: <?php echo $command->target; ?><br />
		Sous titres: <ul>
		<?php foreach( $command->subtitleFiles as $file ) {
			echo "<li>{$file}</li>";
		}
		?>
		</ul><br />
		Videos: <ul>
		<?php foreach( $command->videoFiles as $file ) {
			echo "<li>{$file}</li>";
		}
		?>
		</ul>
		</p>
		<p style="font-family: monospace;"><?php echo $command; ?></p>
	<?php endif; ?>

</frameset>

</body>

