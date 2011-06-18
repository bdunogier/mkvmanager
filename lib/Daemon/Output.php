<?php
/**
 * File containing the mm\Daemon\Output class
 */
namespace mm\Daemon;

class Output
{
    private $fp;

    function __construct( $fp )
    {
        $this->fp = $fp;
        self::$instance = $this;
    }

    function write( $message )
    {
        if ( !is_resource( $this->fp ) )
        {
            throw new Exception( 'Not a resource' );
        }
        fputs( $this->fp, "[" . date('Y/m/d H:i:s') . "] $message\n" );
    }

    /**
     * @return Output
     */
    public static function instance()
    {
        if ( self::$instance !== null )
            return self::$instance;
        else
            throw new RuntimeException( "Output was not instanciated" );
    }

    private static $instance = null;
}
?>