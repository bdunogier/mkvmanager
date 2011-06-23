<?php
namespace mm\Operations;
use mm\Daemon\BackgroundOperation;
use mm\Daemon\QueueItem;
use ezcPersistentSessionInstance;

/**
 * An HTTP file download operation
 *
 * Usage:
 * $download = new mm\Operation\HttpDownload( 'http://example.com/file.zip', '/tmp/file.zip' );
 * mm\Daemon\Queue::add( $download );
 */
class HttpDownload extends Base implements BackgroundOperation
{
    /**
     * Creates an http download operation of $source to $target
     * @param string $source Source URL
     * @param string $target Target path, filename included
     */
    public function __construct( $source, $target )
    {
        $this->source = $source;
        $this->target = $target;
    }

    public function run()
    {
        $outputFp = fopen( 'testfile.iso', 'w' );

        $ch = curl_init( $this->source );

        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_NOPROGRESS, false );
        curl_setopt( $ch, CURLOPT_PROGRESSFUNCTION, array( $this, 'progressCallback' ) );
        curl_setopt( $ch, CURLOPT_FILE, $outputFp );
        curl_exec( $ch );

        fclose( $outputFp );
    }

    /**
     * CURL progress callback
     */
    public function progressCallback( $download_size, $downloaded, $upload_size, $uploaded )
    {
        static $previousProgress = 0;

        if ( $downloaded == 0 )
            $progress = 0;
        else
            $progress = round( $downloaded * 100 / $download_size );

        if ( $progress > $previousProgress)
        {
            $previousProgress = $progress;
            $this->updateQueueItem( $progress );
        }
    }

    public function reset()
    {

    }

    public function __set_state( array $state )
    {
        return new self( $state['source'], $state['target'] );
    }

    public function __toString()
    {
        return "HTTP download: $this->source";
    }

    public $source;
    public $target;
}
?>