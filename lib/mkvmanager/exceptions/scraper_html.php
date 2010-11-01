<?php
/**
 * File containing the tclScraperHTTPException class
 */

/**
 * A scraper HTTP tclScraperHTTPException. Thrown when a non 'OK' HTTP response was obtained.
 */
class MkvManagerScraperHTMLException extends ezcBaseException
{
    public function __construct( $url, $body )
    {
        $this->url = $url;
        $this->body = $body;
        parent::__construct( "An error occured while parsing the fetched HTML" );
    }

    /**
     * The fetched URL
     * @var string
     */
    public $url;

    /**
     * The HTTP body response
     * @var array
     */
    public $body;
}
?>