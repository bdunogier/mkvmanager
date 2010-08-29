<?php
class mmHtmlView extends ezcMvcView
{
	function createZones( $layout )
	{
		$zones = array();
		$zones[] = new ezcMvcPhpViewHandler( 'content', '../templates/test.php' );
		return $zones;
	}
}
?>