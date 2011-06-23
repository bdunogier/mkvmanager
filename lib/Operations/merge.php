<?php
/**
 * File containing the mm\Operations\Merge class
 *
 * @package mm
 * @subpackage Operations
 */

/**
 * An operation that merges source files to an MKV file
 *
 * @package mm
 * @subpackage Operations
 *
 * @property-read MKVMergeCommand commandObject
 * @property-read string targetFile
 * @property-read string targetFileSize
 **/
namespace mm\Operations;
use mm\Daemon\BackgroundOperation;
use mm\Daemon\QueueItem;
use MKVMergeCommand;

class Merge extends Base implements BackgroundOperation
{
    public $command = null;

    /**
     * Constructs a new merge operation for the command $command
     *
     * @param string $command
     */
    public function __construct( $command )
    {
        $this->command = $command;
    }

    public function __get( $property )
    {
        switch( $property )
        {
            case 'commandObject':
                $value = new MKVMergeCommand( $this->command );
                break;

            case 'targetFile':
                $value = $this->commandObject->targetPath;
                break;

            case 'targetFileSize':
                $value = $this->commandObject->TargetSize;
                break;

            default:
                throw new ezcBasePropertyNotFoundException( $property );
        }

        return $value;
    }

    /**
     * Processes the merge operation
     */
    public function run()
    {
        $procFp = popen( $this->command, 'r' );

        $output = array();
        // @todo Add error control
        do
        {
            $line = fread( $procFp, 2048 );
            $output[] = $line;

            // @todo preg_match_all + pick the last one
            if ( preg_match( '/Progress: ([0-9]+)%/', $line, $m ) )
            {
                $progress = (int)$m[1];
                $this->updateQueueItem( $progress, implode( "\n", $output ) );
            }
            usleep( 1000 );
        } while( !feof( $procFp ) );

        pclose( $procFp );

        $this->updateQueueItem( 100 );

        return true;
    }

    public function __set_state( array $state )
    {
        $object = new self( $state['command'] );

        return $object;
    }

    public function reset()
    {

    }

    public function __toString()
    {
        return "MKV merge => {$this->commandObject->title}";
    }
}
?>