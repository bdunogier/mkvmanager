<?php
class mmMvcConfiguration implements ezcMvcDispatcherConfiguration
{
	/**
	 * @return ezcMvcRequestParser
	 */
	function createRequestParser()
	{
	    $parser = new ezcMvcHttpRequestParser;
	    $parser->prefix = preg_replace( '@/index\.php$@', '', $_SERVER['SCRIPT_NAME'] );
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
		$viewHandler = 'mmHtmlView';

		// try the {@see $contentType} found in the URI by {@link runPreRoutingFilters()}
		if ( $this->contentType !== null )
		{
			$viewHandler = $this->acceptTypeViewMapping[$this->contentType];
		}
		// or parse the Accept-Type header to figure out the {@link $contentType}
		else
		{
			foreach( $request->accept->types as $accept )
			{
				if ( isset( $this->acceptTypeViewMapping[$accept] ) )
				{
					$this->contentType = $accept;
					$viewHandler = $this->acceptTypeViewMapping[$accept];
				}
			}
		}
		$view = new $viewHandler( $request, $result );

		switch ( $routeInfo->matchedRoute )
		{
			case '/':
				$view->contentTemplate = 'default.php';
				break;

			case '/mkvmerge2':
				$view->contentTemplate = 'mkvmerge2.php';
				break;

			case '/mkvmerge':
				$view->contentTemplate = 'mkvmerge.php';
				break;
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
		$req->uri = '/fatal';
		$req->variables['exception'] = $response;

	    $result->status = new mmMvcResultStatusNotFound();

		return $req;
	}

	function runResultFilters( ezcMvcRoutingInformation $routeInfo, ezcMvcRequest $request, ezcMvcResult $result )
	{
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
			//else
			//	throw new tclInvalidContentTypeException( $matches['type'] );
		}
	}

	function runRequestFilters( ezcMvcRoutingInformation $routeInfo, ezcMvcRequest $request )
	{
	}

	function runResponseFilters( ezcMvcRoutingInformation $routeInfo, ezcMvcRequest $request, ezcMvcResult $result, ezcMvcResponse $response )
	{
		if ( $this->contentType !== null )
			$response->content = new ezcMvcResultContent( '', $this->contentType, 'utf-8' );

        // handle fatal error responses, setting the appropriate request status result
        if ( $request->uri == '/fatal' )
	        // not found (404) error
            /*if ( $request->variables['exception'] instanceof tclScraperNotFoundException )
	            $response->status = new mmMvcResultStatusNotFound;
	        // unknown error (500)
            else*/
            $response->status = new mmMvcResultStatusError;
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
