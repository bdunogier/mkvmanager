<?php
/**
 * TVEpisodeFile class
 *
 * @version $Id$
 * @copyright 2011
 *
 * @property-read bool hasSubtitleFile
 * @property-read TVEpisodeDownloadedFile downloadedFile filename of the originally downloaded file (release)
 */
class TVEpisodeFile
{
    /**
     * Constructs a TVEpisodeFile based on the filename $filename
     * @param string $filename
     */
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

            case 'downloadedFile':
                $db = ezcDbInstance::get( 'sickbeard' );

                // show ID
                $q = $db->createSelectQuery();
                $q->select( 'tvdb_id' )
                  ->from( 'tv_shows' )
                  ->where( $q->expr->eq( 'show_name', $q->bindValue( $this->showName ) ) );

                /**
                 * @var PDOStatement
                 */
                $stmt = $q->prepare();
                $stmt->execute();
                $showId = $stmt->fetchColumn();

                // downloaded file name
                $q = $db->createSelectQuery();
                $e = $q->expr;
                $q->select( 'resource' )
                  ->from( 'history' )
                  ->where( $e->lAnd(
                      $e->eq( 'action', $q->bindValue( 404 ) ),
                      $e->eq( 'showid', $q->bindValue( $showId) ),
                      $e->eq( 'season', $q->bindValue( $this->seasonNumber) ),
                      $e->eq( 'episode', $q->bindValue( $this->episodeNumber ) )
                  ) );
                $stmt = $q->prepare();
                $stmt->execute();
                return new TVEpisodeDownloadedFile( basename( $downloadedFile = $stmt->fetchColumn() ) );


            default:
                throw new ezcBasePropertyNotFoundException( $property );
        }
    }

    /**
     * TV Show name
     * @var string
     */
    public $showName;

    /**
     * Season number
     * @var integer
     */
    public $seasonNumber;

    /**
     * Episode number
     * @var integer
     */
    public $episodeNumber;

    /**
     * Episode name/title
     * @var string
     */
    public $episodeName;

    /**
     * File extension (mkv, avi)
     * @var string
     */
    public $extension;

    /**
     * Filename, extension included
     * @var string
     */
    public $filename;

    /**
     * Full episode name: <ShowName> - <SeasonNr>x<EpisodeNr> - <EpisodeName> without extension
     * @var string
     */
    public $fullname;
}

?>