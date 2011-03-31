<?php
/**
 * TVEpisodeFile class
 *
 * @version $Id$
 * @copyright 2011
 *
 * @property-read hasSubtitleFile
 */
class TVEpisodeFile
{
    public function __construct( $filename )
    {
        $pathinfo = pathinfo( $filename );
        $this->filename = $filename;
        $this->fullname = $pathinfo['filename'];
        $this->extension = $pathinfo['extension'];
        if ( preg_match( '/^(.*?) - ([0-9]+)x([0-9]+) - (.*)$/', $this->fullname, $matches ) )
            list(, $this->showName, $this->seasonNumber, $this->episodeNumber, $this->episodeName ) = $matches;
    }

    public function __get( $property )
    {
        switch( $property )
        {
            case 'hasSubtitleFile':
                $basedirAndFile = "/home/download/downloads/complete/TV/Sorted/{$this->showName}/{$this->fullname}";
                error_log( "file_exists( $basedirAndFile.srt ) || file_exists( $basedirAndFile.ass )" );
                return ( file_exists( "$basedirAndFile.srt" ) || file_exists( "$basedirAndFile.ass" ) );
                break;

            default:
                throw new ezcBasePropertyNotFoundException( $property );
        }
    }

    public $showName;

    public $seasonNumber;

    public $episodeNumber;

    public $episodeName;

    public $extension;

    /**
     * Filename, extension included
     */
    public $filename;

    /**
     * Full episode name: <ShowName> - <SeasonNr>x<EpisodeNr> - <EpisodeName> without extension
     */
    public $fullname;
}

?>