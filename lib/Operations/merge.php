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

class Merge implements BackgroundOperation
{
    public $command = null;
    public $targetFile = null;
    public $targetFileSize = null;

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
     * Returns a merge operation's progress, as a rounded percentage
     *
     * @return int
     */
    public function progress()
    {
        try {
            $currentTargetSize = mmMkvManagerDiskHelper::bigFileSize( $this->targetFile );
        } catch( ezcBaseFileNotFoundException $e ) {
            return 0;
        }

        return round( $currentTargetSize / $this->targetFileSize * 100 );
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
                error_log( $file['pathname'] );
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

        $result = '';
        $return = '';

        // @todo Use pcntl_exec instead, to avoid errors
        exec( "{$this->command} 2>&1 >/dev/null", $result, $return );

        $status = ( $return !== 0 ) ? -1 : 0;

        return ( $status == 0 );
        // $this->message = implode( "\n", $result );

        // Output::instance()->write( "Done" );
    }

    public function __set_state( array $state )
    {
        $object = new self;
        $object->command = $state['command'];
        $object->targetFile = $state['targetFile'];
        $object->targetFileSize = $state['targetFileSize'];

        return $object;
    }
}
?>