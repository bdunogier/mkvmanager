<?php
class mmAjaxView extends ezcMvcView
{
	function createZones( $layout )
	{
		$zones = array();
		$zones[] = new ezcMvcJsonViewHandler( 'content' );
		return $zones;
	}
}
?>