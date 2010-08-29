<?php
class mmMvcRouter extends ezcMvcRouter
{
	public function createRoutes()
	{
		return array(
		    new ezcMvcRailsRoute( '/',                                  'mmController', 'default' ),
			new ezcMvcRailsRoute( '/test',                              'mmController', 'test' ),
			new ezcMvcRailsRoute( '/fatal',                             'mmController', 'fatal' ),
		);
	}
}
?>