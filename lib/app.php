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
     * @return MKVMergeCommand
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

        return $command->toStruct();
    }

    /**
     * Calculates the best fit for a command
     *
     * @param string $command Windows command line
     * @return array ezcMvcResult content, others... :)
     */
    public static function doBestFit( $command )
    {
        $command = MKVMergeCommandImportWindowsGUI::convert( $command, false );
        $return = array( 'size' => $command->TargetSize );

        if ( $command->conversionType === 'tvshow' )
        {
            $return += mmMkvManagerDiskHelper::BestTVEpisodeFit( $command->title, $command->TargetSize );
        }

        return $return;
    }

    /**
     * Disblayes the files requiring subtitles
     *
     * @return array
     */
    public static function doSubtitles()
    {
        $result = array();
        $result['VideoFiles'] = mmMkvManagerSubtitles::fetchFiles();

        return $result;
    }
}
?>