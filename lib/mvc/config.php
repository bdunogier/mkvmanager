<?php
class mmMvcConfiguration implements ezcMvcDispatcherConfiguration
{
    /**
     * @return ezcMvcRequestParser
     */
    function createRequestParser()
    {
        $parser = new ezcMvcHttpRequestParser;
        $parser->prefix = preg_replace( '@/index\.php$@', '', basename( $_SERVER['SCRIPT_FILENAME'] ) );
        return $parser;
    }

    /**
     * @return ezcMvcRouter
     */
    function createRouter( ezcMvcRequest $request )
    {
        return new mmMvcRouter( $request );
    }

    /**
     * Figures out which view handler should be used.
     *
     * Will use the Accept-Type header to do so, unless a specific Content-Type
     * has been found out in the URI by {@link runPreRoutingFilters()}
     *
     * @return ezcMvcView
     */
    function createView( ezcMvcRoutingInformation $routeInfo,
        ezcMvcRequest $request, ezcMvcResult $result )
    {
        if ( strpos( $routeInfo->matchedRoute, '/ajax/' ) !== false ||
             $routeInfo->matchedRoute == '/nfo/movie/update-info' )
        {
            $view = new mmAjaxView( $request, $result );
        }
        else
        {
            // the part of the route used for the template path of course doesn't include parameters
            $view = new mmHtmlView( $request, $result );
            if ( strpos( $routeInfo->matchedRoute, ':' ) !== false )
                $realRoute = trim( substr( $routeInfo->matchedRoute, 0, strpos( $routeInfo->matchedRoute, ':' ) ), '/' );
            else
                $realRoute = $routeInfo->matchedRoute;
            switch ( $realRoute )
            {
                default:
                    if ( file_exists("../templates/{$realRoute}.php" ) )
                    {
                        $view->contentTemplate = "{$realRoute}.php";
                    }
                    else
                    {
                        $view->contentTemplate = 'default.php';
                        $result->variables['__request'] = $request;
                    }
            }
        }

        return $view;

    }

    /**
     * @return ezcMvcResponseWriter
     */
    function createResponseWriter( ezcMvcRoutingInformation $routeInfo,
        ezcMvcRequest $request, ezcMvcResult $result,
        ezcMvcResponse $response )
    {
        return new ezcMvcHttpResponseWriter( $response );
    }

    /**
     * @return ezcMvcRequest
     */
    function createFatalRedirectRequest( ezcMvcRequest $request,
        ezcMvcResult $result,
        Exception $response )
    {
        $req = clone $request;

        $req->variables = array(
            'status' => 'ko',
            'message' => $response->getMessage(),
            'exception' => print_r( $response, true ) );
        if ( substr( $request->uri, 0, 5 ) == '/ajax' )
        {
            $req->uri = '/ajax/fatal';
        }
        else
            $req->uri = '/fatal';

        $result->status = new mmMvcResultStatusNotFound();

        return $req;
    }

    function runResultFilters( ezcMvcRoutingInformation $routeInfo, ezcMvcRequest $request, ezcMvcResult $result )
    {
        if ( $request->uri == '/fatal' )
        {

        }
    }

    /**
     * Pre-filter the request before routing:
     * - force content type based on a suffix: json, xml or html
     *   has higher priority than the accept-type header
     * @param $request ezcMvcRequest
     */
    function runPreRoutingFilters( ezcMvcRequest $request )
    {
        // force a content type when a known suffix is found at the end of the URI
        if ( preg_match( '/^(?<url1>.*)\.(?<type>json|xml|html)(?<url2>\?.*)?$/', $request->uri, $matches ) )
        {
            if ( isset( $this->suffixContentTypeMapping[$matches['type'] ] ) )
            {
                $this->contentType = $this->suffixContentTypeMapping[ $matches['type'] ];
                $request->uri = $matches['url1'] . $matches['url2'];
            }
        }
    }

    function runRequestFilters( ezcMvcRoutingInformation $routeInfo, ezcMvcRequest $request )
    {
        switch ( $routeInfo->matchedRoute )
        {
            case '/nfo/movie/update-info':
                // @todo Transform back into an object with an eval
                $request->variables['info'] = eval( "return {$_POST['info']};");
                $request->variables['actionType'] = $_POST['actionType'];
                $request->variables['actionValue'] = $_POST['actionValue'];
                break;

            case '/nfo/movie/save/:folder':
                // @todo Transform back into an object with an eval
                $request->variables['info'] = eval( "return {$_POST['info']};");
                break;
        }
    }

    function runResponseFilters( ezcMvcRoutingInformation $routeInfo, ezcMvcRequest $request, ezcMvcResult $result, ezcMvcResponse $response )
    {
        if ( $this->contentType !== null )
            $response->content = new ezcMvcResultContent( '', $this->contentType, 'utf-8' );
        $response->content->charset = 'utf-8';

        // handle fatal error responses, setting the appropriate request status result
        if ( $request->uri == '/fatal' )
        {
            // not found (404) error
            /*if ( $request->variables['exception'] instanceof tclScraperNotFoundException )
               $response->status = new mmMvcResultStatusNotFound;
               // unknown error (500)
               else*/
            $response->status = new mmMvcResultStatusError;
        }
    }

    protected $contentType;

    protected $acceptTypeViewMapping = array(
        'application/json'      => 'mmJsonView',
        'text/html'             => 'mmHtmlView',
        'application/xhtml+xml' => 'mmHtmlView',
        'application/xml'       => 'mmXmlView'
     );

    protected $suffixContentTypeMapping = array(
        'json' => 'application/json',
        'html' => 'text/html',
        'xml'  => 'application/xml'
     );
}
?>
