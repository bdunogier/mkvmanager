<?php
/**
 * Example background operation.
 *
 * Sleeps for $sleep seconds, and writes the string $string to $filename
 **/
namespace mm\Operations;
use mm\Daemon\BackgroundOperation;

class Example implements BackgroundOperation
{
    /**
     * Constructs a new example operation that writes $string to $filename
     *
     * @param string $filename
     * @param string $string
     */
    public function __construct( $string, $sleep = 10, $filename = '/tmp/example_operation.txt' )
    {
        $this->sleep = $sleep;
        $this->string = $string;
        $this->filename = $filename;
    }

    /**
     * Returns a merge operation's progress, as a rounded percentage
     *
     * @return int
     */
    public function progress()
    {
        return trim( array_pop( file( $this->filename ) ) ) * 10;
    }

    /**
     * Processes the merge operation
     */
    public function run()
    {
        $fp = fopen( $this->filename, 'w' );
        for( $i = 1; $i <= 10; $i++ )
        {
            sleep( 1 );
            fputs( $fp, "$i\n" );
        }
        fclose( $fp );
    }

    public function reset()
    {

    }

    public function __set_state( array $state )
    {
        return new self( $state['string'], $state['sleep'], $state['filename'] );
    }

    public $filename;
    public $sleep;
    public $string;

}
?>