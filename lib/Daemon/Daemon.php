<?php
/**
 * File containing the mm\Daemon\Daemon class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/gnu_gpl GNU GPL v2
 * @version //autogentag//
 * @package mm
 */

/**
 * This class executes the daemon and manages the processes
 */
namespace mm\Daemon;
use ezcDbInstance;

class Daemon
{
    public function __construct()
    {
        pcntl_signal( SIGCHLD, array( $this, 'childSignalHandler' ) );
    }

    public function run()
    {
        while( true )
        {
            if ( !$operation = $this->next() )
            {
                // Output::instance()->write( 'No operation' );
                sleep( 1 );
                continue;
            }

            $operation->status = QueueItem::STATUS_RUNNING;
            $operation->startTime = time();
            $operation->update();

            $pid = $this->fork();

            // fork failed
            if ( $pid == -1 )
            {
                Output::instance()->write( 'Error forking process, aborting merge' );
                continue;
            }

            if ( $pid > 0 )
            {
                $this->addJob( $pid, $operation );
                continue;
            }

            Output::instance()->write( "Running operation {$operation->title} [{$operation->hash}]" );
            $operation->run();

            $operation->status = QueueItem::STATUS_DONE;
            $operation->endTime = time();
            $operation->update();

            exit;
        }
    }

    /**
     * Returns the next operation that must be ran
     * @return mm\Daemon\Operation
     */
    public function next()
    {
        // maintain count of how many operations of each type are currently running
        // depending on the priority (#1 = downloads, #2 = merge), return the next operation
        // when an operation finishes, the slot is cleaned up, and one more of the same type can resume

        return Queue::getNextItem();
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

    /**
     * Forks the process, performing the required cleanup operations
     *
     * @return int the result of pcntl_fork()
     */
    public function fork()
    {
        $this->closeDatabaseConnection();

        return pcntl_fork();
    }

    private function closeDatabaseConnection()
    {
        // close the database instance
        $db = ezcDbInstance::get();
        $db = null;
    }

    /**
     * Adds the job to the processes queue
     * @param int $job
     */
    public function addJob( $pid, QueueItem $operation )
    {
        $this->runningOperations[$pid] = $operation;

        // check if this job is in the signal queue already
        if( isset( $this->signalQueue[$pid] ) )
        {
            Output::instance()->write( "found $pid in the signal queue, processing it now" );
            $this->childSignalHandler( SIGCHLD, $pid, $this->signalQueue[$pid] );
            unset( $this->signalQueue[$pid] );
        }
    }

    /**
     * Child process signal handler
     */
    public function childSignalHandler( $signo, $pid = null, $status = null )
    {
        // If no pid is provided, that means we're getting the signal from the system.  Let's figure out
        // which child process ended
        if( $pid === null )
        {
            $pid = pcntl_waitpid( -1, $status, WNOHANG );
        }

        //Make sure we get all of the exited children
        while( $pid > 0 )
        {
            if( $pid && isset( $this->runningOperations[$pid] ) )
            {
                $exitCode = pcntl_wexitstatus( $status );
                if ( $exitCode != 0 )
                {
                    Output::instance()->write( "Process of operation " . $this->runningOperations[$pid]->hash . " exited with status {$exitCode}" );
                    // this is required as the MySQL connection might be closed anytime by a fork
                    // this method is asynchronous, and might be triggered by any signal
                    // the only way is to use a dedicated DB connection, and close it afterwards
                    $this->closeDatabaseConnection();

                    $this->runningOperations[$pid]->reset();

                    $this->closeDatabaseConnection();
                }
                unset( $this->runningOperations[$pid] );
            }
            // A job has finished before the parent process could even note that it had been launched
            // Let's make note of it and handle it when the parent process is ready for it
            // echo "..... Adding $pid to the signal queue ..... \n";
            elseif( $pid )
            {
                $this->signalQueue[$pid] = $status;
            }
            $pid = pcntl_waitpid( -1, $status, WNOHANG );
        }
        return true;
    }

    /**
     * Received signals, for parallel processing
     */
    private $signalQueue = array();

    /**
     * List of currently running operations, indexed by PID
     *
     * @var array(pid=>mm\Daemon\BackgroundOperation)
     */
    private $runningOperations = array();
}
?>
