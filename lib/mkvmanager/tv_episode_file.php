<?php
/**
 * TVEpisodeFile class
 *
 * @version $Id$
 * @copyright 2011
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