<?php
/**
 * TVEpisodeFile class
 *
 * @version $Id$
 * @copyright 2011
 *
 * @property-read bool hasSubtitleFile
 * @property-read string downloadedFile filename of the originally downloaded file (release)
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
                return basename( $downloadedFile = $stmt->fetchColumn() );


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