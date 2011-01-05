#!/usr/bin/env php
<?php
// define( 'DAEMON_MODE', false );
define( 'DAEMON_MODE', true );
include 'config.php';

// check if media target folder is writeable
$storageDir = '/media/aggregateshares/';
if ( !is_writeable( $storageDir ) )
{
    echo "$storageDir can not be written to. Wrong user maybe ?\n";
    die();
}

if ( DAEMON_MODE )
{
    $out = new Output( STDOUT );

    declare( ticks=1 );

    // Trap signals that we expect to recieve
    pcntl_signal( SIGCHLD, 'childHandler' );
    pcntl_signal( SIGUSR1, 'childHandler' );
    pcntl_signal( SIGALRM, 'childHandler' );

    $pid = pcntl_fork();
    if ( $pid < 0 )
    {
        error_log( "unable to fork daemon" );
        $out->write( "ERROR: unable to fork daemon)" );
        exit( 1 );
    }
    /* If we got a good PID, then we can exit the parent process. */
    if ( $pid > 0 )
    {
    // Wait for confirmation from the child via SIGTERM or SIGCHLD, or
    // for two seconds to elapse (SIGALRM).  pause() should not return. */
    pcntl_alarm( 2 );
    sleep( 5 );

    echo "Failed spawning the daemon process\n";
    exit( 1 );
}

    // At this point we are executing as the child process
    $parentProcessID = posix_getppid();

    /* Cancel certain signals */
    pcntl_signal( SIGCHLD, SIG_DFL ); // A child process dies
    pcntl_signal( SIGTSTP, SIG_IGN ); // Various TTY signals
    pcntl_signal( SIGTTOU, SIG_IGN );
    pcntl_signal( SIGTTIN, SIG_IGN );
    pcntl_signal( SIGHUP,  SIG_IGN ); // Ignore hangup signal
    pcntl_signal( SIGTERM, SIG_DFL ); // Die on SIGTERM

    $sid = posix_setsid();
    if ( $sid < 0 )
    {
        error_log( "unable to create a new session" );
        echo "unable to create a new session\n";
        exit( 1 );
    }

    echo "Publishing daemon started. Process ID: " . getmypid() . "\n";

    // stop output completely
    fclose( STDIN );
    fclose( STDOUT );
    fclose( STDERR );

    fclose( $outFP );
    $outFP = fopen( 'log/queue.log', 'a' );
    $out = new Output( $outFP );

    // kill the parent !
    posix_kill( $parentProcessID, SIGUSR1 );

}
else
{
    $out = new Output( STDOUT );
}

while( true )
{
    if ( !$operation = mmMergeOperation::next() )
    {
        sleep( 1 );
        continue;
    }
    $result = '';
	$return = '';

    // mark operation as running
    $operation->status = mmMergeOperation::STATUS_RUNNING;
    $operation->startTime = time();
    ezcPersistentSessionInstance::get()->update( $operation );

    $commandObject = $operation->commandObject;
    $out->write( "Merge: {$commandObject->conversionType} '{$commandObject->title}'" );
    // @todo Use pcntl_exec instead, to avoid errors
    exec( "{$operation->command} 2>&1 >/dev/null", $result, $return );
	$out->write( "Done" );

	$status = ( $result !== 0 ) ? -1 : 0;

	$operation->status = ( $status == 0 ) ? mmMergeOperation::STATUS_DONE : mmMergeOperation::STATUS_ERROR;
    $operation->message = implode( "\n", $result );
    $operation->endTime = time();
    ezcPersistentSessionInstance::get()->update( $operation );

	unset( $result, $return, $operation, $commandObject );
}

/**
 * Signal handler
 * @param int $signo Signal number
 */
function childHandler( $signo )
{
    switch( $signo )
    {
        case SIGALRM: exit( 1 ); break;
        case SIGUSR1: exit( 0 ); break;
        case SIGCHLD: exit( 1 ); break;
    }
}

class Output
{
    private $fp;

    function __construct( $fp )
    {
        $this->fp = $fp;
    }

    function write( $message )
    {
        if ( !is_resource( $this->fp ) )
        {
            throw new Exception( 'Not a resource' );
        }
        fputs( $this->fp, "[" . date('Y/m/d H:i:s') . "] $message\n" );
    }
}
?>