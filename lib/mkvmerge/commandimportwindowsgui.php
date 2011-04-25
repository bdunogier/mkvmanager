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
     * Converts a windows GUI generated command to a normal one
     * @param string $windowsCommand
     * @param string $targetDisk
     * @return MKVMergeCommand
     */
    public static function convert( $windowsCommand, $targetDisk )
    {
        $command = $windowsCommand;

        self::_convertExecutable( $command );
        self::_convertSlashes( $command );
        self::_convertSourceFolder( $command );
        self::_convertTargetFolder( $command, $targetDisk );
        self::_checkSubtitlesCharset( $command );

        return new MKVMergeCommand( $command );
    }

    /**
    * Applies the correct charset to the subtitles if not already done
    */
    protected function _checkSubtitlesCharset( &$command )
    {
        $commandParts = explode( '" "', $command );
        for( $i = count( $commandParts ) - 1; $i >= 0; $i-- )
        {
            // subtitle found
            if ( preg_match( '/(ass|srt)$/', $commandParts[$i] ) )
            {
                $subFileName = $commandParts[$i];

                $charsetFound = false;

                // go backwards until another file / the executable is found
                for( $j = $i - 1; $j >= 0; $j-- )
                {
                    if ( preg_match( '/(ass|srt|mkv|exe)$/', $commandParts[$i] ) )
                        break;
                    elseif ( $commandParts[$i] == '--sub-charset' )
                    {
                        if ( $commandParts[$i + 1] !== 'default' )
                            $charsetFound = true;
                    }
                }

                /**
                * Add the charset
                */
                if ( !$charsetFound )
                {
                    $finfo = new finfo( FILEINFO_MIME_ENCODING );
                    $encoding = strtoupper( $finfo->file( $subFileName ) );
                    // assume iso by default
                    if ( $encoding == 'UNKNOWN-8BIT' )
                        $encoding = "ISO-8859-1";
                    $command = str_replace( "\"{$subFileName}\"", "\"--sub-charset\" \"0:{$encoding}\" \"{$subFileName}\"", $command );
                }
            }
        }
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
        $command = str_replace( '\\\\', '/', $command );
        $command = str_replace( '\\', '/', $command );
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
        if ( $targetDisk === false )
            $targetDisk = '<NONE>';
        $replace = "/media/storage/{$targetDisk}/";
        $replace .= ( self::_extractType( $command ) == 'movie' ? 'Movies' : 'TV Shows' ) . DIRECTORY_SEPARATOR;
        $command = str_replace( 'F:/', $replace, $command );
    }

    /**
     * Analyzes the command and stores the conversion type (movie / tvshow)
     * in self::$conversionType
     */
    protected static function _extractType( &$command )
    {
        if ( strpos( $command, '/complete/Movies/' ) !== false )
        {
            return 'movie';
        }
        if ( strpos( $command, '/complete/TV/' ) !== false )
        {
            return 'tvshow';
        }
        else
            throw new Exception( "Unable to extract the conversion type" );
    }
}

?>