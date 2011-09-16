<?php
/**
 * Example background operation.
 *
 * Sleeps for $sleep seconds, and writes the string $string to $filename
 **/
namespace mm\Operations;
use mm\Daemon\BackgroundOperation;
use mm\Xbmc\Nfo\Writers\Movie as NfoWriterObject;

class NfoWriter extends Base implements BackgroundOperation
{
    /**
     * Constructs a new NfoWriter operation that writes the NFO from $nfoWriter to $filePath
     *
     * @param NfoWriter $filename
     * @param string $filepath
     */
    public function __construct( NfoWriterObject $nfoWriter, $filepath )
    {
        $this->nfoWriter = $nfoWriter;
        $this->filepath = $filepath;
    }

    public function progress()
    {
        return ( file_exists( $this->filepath ) ? 100 : 0 );
    }

    /**
     * Processes the merge operation
     */
    public function run()
    {
        $this->nfoWriter->write( $this->filepath );
    }

    public function reset()
    {

    }

    public function __set_state( array $state )
    {
        return new self( $state['string'], $state['sleep'], $state['filename'] );
    }

    public function __toString()
    {
        return "Write NFO => {$this->filepath}";
    }

    public $nfoWriter;
    public $filepath;

}
?>