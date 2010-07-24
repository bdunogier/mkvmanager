<?php
/**
 * File containing the MKVMergeCommandImportWindowsGUI
 *
 * @version $Id$
 * @copyright 2010
 */

/**
 * This class will generate a MKVMergeCommand out of a windows GUI generated
 * command line
 */
class MKVMergeCommandImportWindowsGUI
{

	/**
	 * Constructor
	 */
	public static function convert( $windowsCommand, $targetDisk )
	{
		$command = $windowsCommand;

		self::_convertExecutable( $command );
		self::_convertSlashes( $command );
		self::_convertSourceFolder( $command );
		self::_convertTargetFolder( $command, $targetDisk );

		return new MKVMergeCommand( $command );
	}

	/**
	 * Converts the executable from windows to linux
	 * @return void
	 */
	protected static function _convertExecutable( &$command )
	{
		$command = str_replace( '"D:\Program Files\MKVtoolnix\mkvmerge.exe"', 'mkvmerge', $command );
	}

	protected static function _convertSlashes( &$command )
	{
		$command = str_replace( '\\', '/', $command );
		$command = str_replace( '\\\\', '/', $command );
	}

	protected static function _convertSourceFolder( &$command )
	{
		$command = str_replace(
			array( 'X:/complete/', '//FORTRESS/Downloads/complete/' ),
			'/home/download/downloads/complete/',
			$command );
	}

	protected static function _convertTargetFolder( &$command, $targetDisk )
	{
		$replace = "/media/storage/{$targetDisk}/";
		$replace .= ( self::_extractType( $command ) == 'movie' ? 'Movies' : 'TV Shows' ) . '/';
		$command = str_replace( 'F:/', $replace, $command );
	}

	/**
	 * Analyzes the command and stores the conversion type (movie / tvshow)
	 * in self::$conversionType
	 */
	protected static function _extractType( &$command )
	{
		if ( strstr( $command, '/complete/Movies/' ) !== false )
		{
			return 'movie';
		}
		if ( strstr( $command, '/complete/TV/' ) !== false )
		{
			return 'tvshow';
		}
		else
			throw new Exception( "Unable to extract the conversion type" );
	}
}

?>