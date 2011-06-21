<?php
/**
* SQL Table:
* CREATE TABLE commands (
* hash TEXT PRIMARY KEY,
* start_time INTEGER,
* end_time INTEGER,
* command TEXT,
* target_file TEXT,
* target_file_size INTEGER,
* pid INTEGER,
* status INTEGER,
* message TEXT );
*
* @property-read MKVMergeCommand commandObject
**/
namespace mm\Operations;
use mm\Daemon\BackgroundOperation;
use mm\Daemon\QueueItem;
use MKVMergeCommand;

class Merge implements BackgroundOperation
{
    public $command = null;
    public $targetFile = null;
    public $targetFileSize = null;

    /**
     * @var mm\Daemon\QueueItem
     */
    private $queueItem;

    /**
     * Constructs a new merge operation for the command $command
     *
     * @param string $command
     */
    public function __construct( $command )
    {
        $this->command = $command;

        $commandObject = new MKVMergeCommand( $command );
        $this->targetFile = $commandObject->targetPath;
        $this->targetFileSize = $commandObject->TargetSize;
    }

    public function __get( $property )
    {
        switch( $property )
        {
            case 'commandObject':
                $value = new MKVMergeCommand( $this->command );
                break;

            default:
                throw new ezcBasePropertyNotFoundException( $property );
        }

        return $value;
    }

    /**
     * Checks if any of the source files still exist
     *
     * @return bool
     */
    public function sourceFilesExist()
    {
        $return = false;

        $command = $this->commandObject;
        $files = array_merge( $command->VideoFiles, $command->SubtitleFiles );
        foreach( $files as $file )
        {
            if ( file_exists( $file['pathname'] ) && filesize( $file['pathname'] ) > 0 )
            {
                $return = true;
                break;
            }
        }
        return $return;
    }

    /**
     * Processes the merge operation
     */
    public function run()
    {
        // Output::instance()->write( "Merge: {$this->commandObject->conversionType} '{$this->commandObject->title}'" );

        $procFp = popen( $this->command, 'r' );

        $output = array();
        // @todo Add error control
        do
        {
            $line = fread( $procFp, 2048 );
            $output[] = $line;
            if ( preg_match( '/Progress: ([0-9]+)%/', $line, $m ) )
            {
                $this->queueItem->progress = (int)$m[1];
            }
            $this->queueItem->message = implode( "\n", $output );
            $this->queueItem->update();
            usleep( 1000 );
        } while( !feof( $procFp ) );

        pclose( $procFp );

        return true;
        // return ( $status == 0 );
        // $this->message = implode( "\n", $result );

        // Output::instance()->write( "Done" );
    }

    public function __set_state( array $state )
    {
        $object = new self( $state['command'] );
        $object->targetFile = $state['targetFile'];
        $object->targetFileSize = $state['targetFileSize'];

        return $object;
    }

    public function reset()
    {

    }

    public function setQueueItem( QueueItem $queueItem )
    {
        $this->queueItem = $queueItem;
    }
}
?>