<?php
/**
 * File containing the mmMkvManagerSubtitles class
 */

/**
 *
 */
class mmMkvManagerSubtitles
{
    public static function fetchFilesWithoutSubtitles()
    {
        try {
            /*$directoryIterator = new RecursiveDirectoryIterator( '/home/download/downloads/complete/TV/Sorted' );

            $iterator = new RecursiveIteratorIterator( $directoryIterator );*/
            try {
                //foreach( new UnsortedEpisodesFilter( $iterator ) as $file )
                foreach( glob( "/home/download/downloads/complete/TV/Sorted/*/*.{mkv,avi}", GLOB_BRACE ) as $file )
                {
                    print_r( $file );
                }
            }
            catch( Exception $e )
            {
                print_r( $e );
            }
        }
        catch( Exception $e )
        {
            echo "An exception has occured: " . $e->getMessage() . "<br />";
        }

        return array();
    }
}

?>