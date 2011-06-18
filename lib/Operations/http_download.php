<?php
namespace mm\Operation;
use mm\Daemon\BackgroundOperation;

/**
 * An HTTP file download operation
 *
 * Usage:
 * $download = new mm\Operation\HttpDownload( 'http://example.com/file.zip', '/tmp/file.zip' );
 * mm\Daemon\Queue::add( $download );
 */
class HttpDownload implements BackgroundOperation
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
        $ch = curl_init( $this->source );
        curl_setopt( $ch, CURLOPT_NOPROGRESS, false );
        curl_setopt( $ch, CURLOPT_PROGRESSFUNCTION, array( $this, 'progressCallback' ) );
        curl_exec( $ch );
    }

    /**
     * CURL progress callback
     */
    public function progressCallback( $download_size, $downloaded, $upload_size, $uploaded )
    {

    }

    public $source;
    public $target;
}
?>