<?php
/**
 * File containing the UnsortedEpisodesFilter class
 */

/**
 * Filters out episodes who need some love:
 * - avi|mkv extension
 * - no matching .srt file
 * - bigger than 25 MB
 */
class UnsortedEpisodesFilter extends FilterIterator
{
    /**
     * UnsortedEpisodesFilter::accept()
     *
     * @return
     */
    public function accept()
    {
        $file = new SplFileInfo( $this->getInnerIterator()->current() );

        if ( !$file->isFile() )
        {
            echo "Not a file<br />";
            return false;
        }


        if ( !preg_match( '/\.(mkv|avi)$/ ', $file->getBasename() ) )
        {
            echo "Not a video<br />";
            return false;
        }

        if ( $file->getSize() < ( 25 * 1024 * 1024 ) )
        {
            echo "Too small<br />";
            return false;
        }

        $subtitleFileNames = array( $file->getBasename() . '.srt', $file->getBasename() . '.ass' );
        foreach ( $subtitleFileNames as $subtitleFileName )
            if ( file_exists( $subtitleFileName ) )
                return false;
    }
}
?>