<?php
/**
 * Application class
 *
 * @version $Id$
 * @copyright 2010
 */
class mmApp
{
	/**
	 * Converts a windows CMD variable
	 *
	 * @param string $winCMD
	 * @param string $target
	 * @return array results
	 */
	public static function doConvertWinCMD( $winCmd, $target )
	{
		try {
			$command = MKVMergeCommandImportWindowsGUI::convert( $winCmd, $target );
		} catch ( Exception $e ) {
			$exceptionMessage = $e->getMessage();
			$callstack = $e->getTraceAsString();
			$message = <<<EOF
<p class="error">An exception has occured in <span class="filename">{$e->getFile()}:{$e->getLine()}</p>
<p class="error message">{$exceptionMessage}</p>
<pre class="error dump">{$callstack}</pre>
EOF;
			throw new Exception( $message );
		}

		// symlink
		$command->appendSymLink = true;
		$command->appendMessage = true;

		return array( 'command' => $command );
	}
}
?>