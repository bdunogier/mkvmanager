<?php
/**
 * Base HTML scraper class
 * Must be extended by implementing the get() method that returns the scraping result
 *
 * @property-read string url The requested URL
 */
abstract class MkvManagerScraper
{
    protected $params = array();
    protected $baseURL = '';
    protected $requestUrl;
    protected $responseBody;
    protected $responseHeaders;

    const HTTP_OK = 1;
    const HTTP_KO = 2;

    /**
     * Builds the fetch URL, and returns the resulting as a SimpleXML Object
     *
     * @param string $url An URL to use. Will fallback to $requestUrl if not given.
     *
     * @return SimpleXMLElement
     * @throws tclScraperNetworkException If the URL couldn't be fetched
     */
    protected function fetch( $url = null )
    {
        $this->requestUrl = $url !== null ? $url : $this->baseURL;
        if ( count( $this->params ) > 0 )
        {
            foreach( $this->params as $key => $value )
                $URIComponents[] = "$key=" . urlencode( $value );
            $this->requestUrl .= '?' . implode( '&', $URIComponents );
        }

        try {
            //$cache = ezcCacheManager::getCache( 'scrapers' );
        } catch( Exception $e ) {
            throw $e;
        }
        $cacheId = md5( $this->requestUrl );

        //if ( ( $this->responseBody = $cache->restore( $cacheId ) ) === false )
        //{
            set_error_handler( array( $this, 'phpFileGetContentsErrorHandler' ) );
            $this->responseBody = @file_get_contents( $this->requestUrl, 0, stream_context_create( array(
                'http' => array( 'timeout' => 5 )
            ) ) );
            restore_error_handler();

            $this->responseHeaders = $http_response_header;

            if( $this->HTTPStatus(  ) != self::HTTP_OK )
                throw new MkvManagerScraperHTTPException( $this->requestUrl, $this->responseHeaders );
            //$cache->store( $cacheId, $this->responseBody );
        //}
        $doc = new DOMDocument();
        $doc->strictErrorChecking = FALSE;
        if ( @$doc->loadHTML( $this->responseBody ) === false )
            throw new MkvManagerScraperHTMLException( $this->requestUrl, $this->responseBody );

        $doc = simplexml_import_dom( $doc );

        return $doc;
    }

    /**
     * Extracts the HTTP Status from $http_response_headers
     * @return int self::HTTP_OK, or self::HTTP_KO
     */
    public function HTTPStatus()
    {
        if( substr( $this->responseHeaders[0], -2 ) == 'OK' )
            return self::HTTP_OK;
        else
            return self::HTTP_KO;
    }

    /**
     * Custom error handler used for the file_get_contents call
     *
     * @param mixed $errno
     * @param mixed $errstr
     * @param mixed $errfile
     * @param mixed $errline
     * @return void
     */
    public function phpFileGetContentsErrorHandler( $errno, $errstr, $errfile, $errline, $errcontext )
    {

        if ( $errno === E_WARNiNG )
            throw new MkvManagerScraperNetworkException( $errcontext['url'] );
        else
            return false;
    }

    public function __get( $property )
    {
        switch( $property )
        {
            case 'url':
                return $this->requestUrl;
                break;
            default:
                throw new ezcBasePropertyNotFoundException( $property );
        }
    }

    /**
     * The main function that executes the scrap and returns the results
     * @return mixed
     */
    abstract public function get();
}
?>