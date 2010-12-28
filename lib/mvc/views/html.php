<?php
class mmHtmlView extends ezcMvcView
{
	function createZones( $layout )
	{
		$zones = array();
		$zones[] = new ezcMvcPhpViewHandler( 'content', '../templates/' . $this->contentTemplate );
		$zones[] = new ezcMvcPhpViewHandler( 'page_layout', '../templates/pagelayout.php' );
		return $zones;
	}
}
?>