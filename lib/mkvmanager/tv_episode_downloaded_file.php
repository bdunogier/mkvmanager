<?php
/**
 * TVEpisodeDownlooadedFile class
 *
 * @version $Id$
 * @copyright 2011
 *
 * @property-read bool hasSubtitleFile
 * @property-read string downloadedFile filename of the originally downloaded file (release)
 */

/**
 * Class that represents a downloaded TV episode file
 */
class TVEpisodeDownloadedFile
{
    /**
     * Constructs a TVEpisodeDownloadedFile based on the filename $filename
     * @param string $filename
     */
    public function __construct( $filename )
    {
        $pathinfo = pathinfo( $filename );
        $this->filename = $filename;

        if ( preg_match( "/-(.*)\.[a-z]{3}$/", $filename, $m ) )
            $this->releaseGroup = strtolower( $m[1] );
    }

    public function __toString()
    {
        return $this->filename;
    }

    /**
     * Tests if the subtitle file $subtitleFile seems to match this release
     * @param string $subtitleFile
     * @return bool
     */
    public function matchesSubtitle( $subtitleFile )
    {
        $separator = "[-\.]";
        $releaseGroupPattern = implode( '|', $this->releaseGroupWithAliases() );
        $pattern = "/{$separator}({$releaseGroupPattern}){$separator}/i";
        return preg_match( $pattern, $subtitleFile );
    }

    /**
     * Returns the release group with its subtitle name aliases (ex. DIMENSION => DIM)
     */
    private function releaseGroupWithAliases()
    {
        switch ( $this->releaseGroup )
        {
            case 'dimension':
                return array( 'dimension', 'dim' );
                break;

            default:
                return array( $this->releaseGroup );
        }
    }

    /**
     * Filename, extension included
     * @var string
     */
    public $filename;

    /**
     * Release group
     * @var string
     */
    public $releaseGroup;
}

?>