<?php
/**
 * TVEpisodeFile class
 *
 * @version $Id$
 * @copyright 2011
 *
 * @property-read bool $hasSubtitleFile
 * @property-read TVEpisodeDownloadedFile $downloadedFile filename of the originally downloaded file (release)
 * @property-read string $path the file's full path
 * @property-read string $subtitleFile the file's subtitle, if it exists
 * @property-read double $fileSize the episode file's size
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
        if ( preg_match( '/^(.*?) - [sS]?([0-9]+)[xXeE]([0-9]+)(-[0-9]+)? - (.*)$/', $this->fullname, $matches ) )
            list(, $this->showName, $this->seasonNumber, $this->episodeNumber, $this->episodeNumber2, $this->episodeName ) = $matches;
        $this->checkValidity();
    }

    /**
     * Loop against properties provided by requiredProperties()
     * Set isValid to false if at least one was null
     *
     * @return void
     */
    private function checkValidity()
    {
        $valid = true;
        foreach( self::requiredProperties() as $requiredProperty )
        {
            if( is_null( $this->$requiredProperty ) )
            {
               $valid = false;
               break;
            }

        }
        $this->isValid = $valid;
    }

    /**
     * Return an array of the required properties needed to be a valid TVEpisodeFile object
     * Used by checkValidity()
     *
     * @return array
     */
    static public function requiredProperties()
    {
		return array( 'showName', 'seasonNumber', 'episodeNumber', 'episodeName', 'fullname' );
    }

    public function __get( $property )
    {
    	$tvShowPath = ezcConfigurationManager::getInstance()->getSetting( 'tv', 'GeneralSettings', 'SourcePath' );
        switch( $property )
        {
            case 'hasSubtitleFile':
                $basedirAndFile = "{$tvShowPath}/{$this->showName}/{$this->fullname}";
                return ( file_exists( "$basedirAndFile.srt" ) || file_exists( "$basedirAndFile.ass" ) );
                break;

            case 'subtitleFile':
                $basedirAndFile = "{$tvShowPath}/{$this->showName}/{$this->fullname}";
                if ( file_exists( "$basedirAndFile.ass" ) )
                    return "$basedirAndFile.ass";
                elseif ( file_exists( "$basedirAndFile.srt" ) )
                {
                    return "$basedirAndFile.srt";
                }
                else
                {
                    throw new Exception("No subtitle found for $this->filename" );
                }
                break;

            case 'path':
                return "{$tvShowPath}/{$this->showName}/{$this->filename}";
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

            case 'fileSize':
                return mmMkvManagerDiskHelper::bigFileSize( $this->path );

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
     * Episode number for double episode such as SXXEYY-ZZ
     * @var integer
     */
    public $episodeNumber2;

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

    /**
     * True if the episode has been successfully identified
     * @var boolean
     */
    public $isValid;
}

?>
