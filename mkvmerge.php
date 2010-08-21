<?php
/**
* @todo Add auto charset
* @todo Add auto detection of TV Show title
*/

function perl_filesize($filename)
{
	return exec("
	        perl -e 'printf \"%d\n\",(stat(shift))[7];' ".$filename."
	");
}
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
		</style>
</head>
<body>
<h1>MKV Merger</h1>

<frameset>
	<legend>Status</legend>
	<?php
	$db = new SQLite3( "tmp/mergequeue.db" );
	$array = $db
		->query( "SELECT * FROM `commands` WHERE `pid` = 0" )
		->fetchArray( SQLITE3_ASSOC );
	echo "<pre>"; var_dump( $array ); echo "</pre>";
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
				// var_dump( $disk );
				if ( $disk->isDot() )
					continue;
				$freespace = decodeSize( disk_free_space( $disk->getPathname() ) );
				$diskName = $disk->getFilename();
				$selectedText = ( $diskName == $_POST['Target'] ) ? ' selected="selected"' : '';
				echo "<option value=\"{$diskName}\"{$selectedText}>{$diskName} ({$freespace} libres)</option>\n";
			}
			?>
			</select>
			<p><input type="checkbox" name="QueueCommand" value="1" id="chkQueueCommand" /><label for="chkQueueCommand">Add to queue</label></p>
		</p>
		<p><input type="submit" name="ConvertWinCmd" /></p>
	</form>
	<?php
	if ( $_POST['Target'] == "0" )
	{
		die( 'Target is mandatory' );
	}

	if ( isset( $_POST['ConvertWinCmd'] ) ):
		$command = $_POST['WinCmd'];

		// Executable
		$command = str_replace( '"D:\Program Files\MKVtoolnix\mkvmerge.exe"', 'mkvmerge', $command );

		// all double backslashes
		$command = str_replace( '\\\\', '/', $command );

		// Détection Movies / TV Shows
		if ( strstr( $command, '/complete/Movies/' ) !== false )
		{
			$type = "Movie";

			// F: = cible
			$command = str_replace( 'F:/', '/media/storage/'.$_POST['Target'].'/Movies/', $command );

			// X:\\complete\\ = /home/download/downloads/complete/
			$command = str_replace( array( 'X:/complete/', '//FORTRESS/Downloads/complete/' ), '/home/download/downloads/complete/', $command );

			// parse the command to get the target / sources
			if ( !preg_match( '#/media/storage/[^/]+/Movies/([^/]+)/\1\.(avi|mkv)#', $command, $matches ) )
				// throw new Exception("Unable to identify the target in the transformed command" );
				die("Unable to identify the target in the transformed command: $command" );

			$title = $matches[1];
			$target = dirname( $matches[0] );
			$linkTarget = "/media/aggregateshares/Movies/";
		}
		elseif ( strstr( $command, '/complete/TV/' ) !== false )
		{
			$type = "TV Shows";

			$command = str_replace( 'F:/', '/media/storage/'.$_POST['Target'].'/TV Shows/', $command );

			// X:\\complete\\ = /home/download/downloads/complete/
			$command = str_replace( array( 'X:/complete/', '//FORTRESS/Downloads/complete/' ), '/home/download/downloads/complete/', $command );

			// parse the command to get the target / sources
			if ( !preg_match( '#/media/storage/[^/]+/TV Shows/([^/]+)/([^/]+)\.(avi|mkv)#', $command, $matches ) )
				echo "Failed matching the target";
			$showName = $matches[1];
			$episodeName = $matches[2];
			$title = $episodeName;
			$target = $matches[0];
			$linkTarget = "/media/aggregateshares/$type/$showName/";
		}
		/*if (!preg_match_all( '#/home/download/downloads/complete/Movies/[^/]+/[^/]+\.(mkv|avi|srt\ssa)#', $command, $matches ) )
	   	echo "No matches";*/

		/*$sources = $matches[0];
		$targetSize = 0;
		echo "S: " . perl_filesize( $sources[0] ) . "\n";
		foreach( $sources as $source )
		{
			$targetSize += perl_filesize( $source );
		}
		$targetDiskSpace = disk_free_space( "/media/storage/{$_POST['Target']}/Movies/" );
		if ( $targetDiskSpace < $targetSize )
			echo "<p style=\"font-weight: bold; color: red;\">Espace disque insuffisant pour ce fichier (" . decodeSize( $targetDiskSpace ) . " libres, " . decodeSize( $targetSize ). " requis)";*/

		if ( isset( $_POST['QueueCommand'] ) )
		{
			$db = new SQLite3( "tmp/mergequeue.db" );
			$db->query( $query = "INSERT INTO commands (`time`, `command`, `status` ) VALUES( strftime('%s','now'), ".
				"'" . $db->escapeString( $command ) . "'" .
				", 0)" );
			echo "<p><b>Command inserted</p></b>\n";
		}

		// symlink
		$command .= "; sudo -u media ln -s \"{$target}\" \"$linkTarget\"";
		$command .= "; echo \"Done converting {$title}\"";
		?>
		<p>
		Titre: <?php echo $title; ?><br />
		Cible: <?php echo $target; ?><br />
		</p>
		<p style="font-family: monospace;"><?php echo $command; ?></p>
	<?php endif; ?>

</frameset>

</body>

