<?php
/*$pid = pcntl_fork();

// parent process, register executed process
if ( $pid )
{
	sleep( 1 );
	while ( ( $childStatus = pcntl_waitpid( $pid, $status, WNOHANG ) ) == 0 )
	{
		echo "Child is still working\n";
//		$fp = fopen( "tmp/pid-$pid", 'rb' );
//		while( $line = fgets( $fp, 4096 ) )
//			echo $line;
//		fclose( $fp );
		echo "Sleeping for a bit now...\n";
		sleep( 2 );
	}
	echo "P: We are somehow done\n";
	echo "P: ";
	var_dump( array( "childStatus" => $childStatus ) );

}
// child process
else
{
*/
	$myPid = posix_getpid();

	// get next command from DB
	$db = new SQLite3( "tmp/mergequeue.db" );
	$res = $db->query( "SELECT `time`, `command` FROM `commands` WHERE `pid` = -1 ORDER BY `time` DESC LIMIT 0,1" );
	if ( $res === false )
		echo "**** SQLite error\n";
	$row = $res->fetchArray( SQLITE3_ASSOC );
	$db->query( "UPDATE commands SET `pid` = $myPid WHERE `time` = {$row['time']}" );
	echo "[" . date('H:i:s') . "] Starting conversion\n";
	exec( "{$row['command']} > tmp/pid-{$myPid}" );
	echo "[" . date('H:i:s') . "] Conversion finished\n";
	$db->query( "UPDATE commands SET `pid` = 0 WHERE `time` = {$row['time']}" );
	unlink( "tmp/pid-{$myPid}" );

	// @todo Add support for symlink creation
	// need a lib that gets the target, sources, etc, out of a command
}
?>