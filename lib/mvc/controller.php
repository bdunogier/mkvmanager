<?php
class mmController extends ezcMvcController
{
    public function doDefault()
    {
        $res = new ezcMvcResult;
        $res->variables['test'] = 'test';
        return $res;
    }

    /**
     * Returns the lines list, with links to further details about each
     * @return ezcMvcResult
     */
    public function doLignes()
    {
        $result = new ezcMvcResult;

        $scrapperLignes = new tclScraperLignes();
        $result->variables['lignes'] = $scrapperLignes->get();
        $result->variables['tcl-url'] = $scrapperLigne->url;

        return $result;
    }

    public function doFatal()
    {
        $result = new ezcMvcResult;
        $result->variables['exception'] = $this->request->variables['exception'];
        return $result;
    }

	public function doTest()
	{
		echo "HEY";
		return new ezcMvcResult();
	}

	public function doMkvMerge()
	{
		// $res = new ezcMvcResult;
		// $res->variables['test'] = 'test';
		$result = new ezcMvcResult;
		$result->variables['targetDisks'] = self::diskList();
		if ( isset( $_POST['WinCmd'] ) )
			$result->variables = array_merge( $result->variables, mmApp::doConvertWinCMD( $_POST['WinCmd'], $_POST['Target'] ) );
		return $result;
	}

	/**
	 * Return the list of available storage drives, including their free space
	 * @return array( stdClass ) properties: name, freespace, selectedText
	 *
	 * @todo Handle seletedText differently
	 * @todo Move somewhere else
	 */
	protected static function diskList()
	{
		$dir = opendir( '/media/storage/' );
		foreach( new DirectoryIterator( '/media/storage/' ) as $disk )
		{
			$target = isset( $_POST['Target'] ) ? $_POST['Target'] : false;
			// var_dump( $disk );
			if ( $disk->isDot() )
				continue;
			$freespace = self::decodeSize( disk_free_space( $disk->getPathname() ) );
			$diskName = $disk->getFilename();
			$selectedText = ( $diskName == $target ) ? ' selected="selected"' : '';
			$disk = new stdClass();
			$disk->name = $diskName;
			$disk->freespace = $freespace;
			$disk->selectedText = $selectedText;

			$return[] = $disk;
		}
		return $return;
	}

	/**
	 * Transforms a numnber of bytes in a readable file size
	 * @param int $bytes
	 * @return string
	 *
	 * @todo Move somewhere else
	 */
	protected static function decodeSize( $bytes )
	{
		$types = array( 'B', 'KO', 'MO', 'GO', 'TO' );
		for( $i = 0; $bytes >= 1024 && $i < ( count( $types ) -1 ); $bytes /= 1024, $i++ );
		return round( $bytes, 2 ) . " " . $types[$i];
	}

}
?>
