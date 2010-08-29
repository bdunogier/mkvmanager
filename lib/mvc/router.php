<?php
class mmMvcRouter extends ezcMvcRouter
{
	public function createRoutes()
	{
		return array(
		    new ezcMvcRailsRoute( '/',         'mmController', 'mkvMerge' ),
			new ezcMvcRailsRoute( '/test',     'mmController', 'test' ),
			new ezcMvcRailsRoute( '/fatal',    'mmController', 'fatal' ),
			new ezcMvcRailsRoute( '/mkvmerge', 'mmController', 'mkvMerge' ),
		);
	}
}
?>