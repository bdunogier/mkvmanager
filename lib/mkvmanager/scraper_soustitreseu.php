<?php
class MkvManagerScraperSoustitreseu extends MkvManagerScraper
{
    /**
     * Constructs a new sous-titres.eu scraper
     *
     * @param string $searchCriteria
     *        One of:
     *        - TV Show filename (<series> - <season>x<episode>...)
     *        - BetaSeries ID: theitcrowd, southpark...
     * @param string $release The release filename
     *
     * @todo Improve criteria to a named struct so that the scraper behaviour can
     *       be changed depending on the info we have
     */
    public function __construct( $searchCriteria, $release )
    {
        // space: we have a human readable series name
        if ( strpos( $searchCriteria, ' ' ) !== false )
        {
            $tvShowEpisode = new TVEpisodeFile( $searchCriteria );

            $this->searchShowCode = preg_replace( array( "/[^a-z0-9 ]/i", "/ /" ), array( '', '_' ), strtolower( $tvShowEpisode->showName ) );
	    if ( isset( $this->aliases[ $this->searchShowCode ] ) )
		$this->searchShowCode = $this->aliases[ $this->searchShowCode ];

            $this->fileName = $searchCriteria;
            $this->searchSeason = $tvShowEpisode->seasonNumber;
            $this->searchEpisode = $tvShowEpisode->episodeNumber;

            $this->baseURL .= "{$this->searchShowCode}.html";

            if ( $release )
                $this->release = new TVEpisodeDownloadedFile( $release );
        }
    }

    public function get()
    {
        try {
            $doc = $this->fetch();
        } catch ( MkvManagerScraperHTMLException $e ) {
            throw $e;
        }

        $episodeNum = sprintf( '%dx%02d', $this->searchSeason, $this->searchEpisode );

        $ret = array();
        $res = $doc->xpath( "//span[text()='$episodeNum']/ancestor::a[//span/img[@title='fr']]" );

        // no subtitles for this file
        if ( !count( $res ) )
            return false;

        $link = $res[0];
        $subtitleName = (string)$link;
        $url = (string)$link['href'];
        $zipUrl = "http://www.sous-titres.eu/series/{$url}";

        // download to temporary folder
        $targetPath = '/tmp/' . md5( $subtitleName ) . '.zip';
        $fp = fopen( $targetPath, 'wb' );
        $ch = curl_init( $zipUrl );
        curl_setopt( $ch, CURLOPT_URL, $zipUrl );
        curl_setopt( $ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.10 (KHTML, like Gecko) Chrome/8.0.552.18 Safari/534.10' );
        curl_setopt( $ch, CURLOPT_REFERER, $this->baseURL );
        curl_setopt( $ch, CURLOPT_FILE, $fp );
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
        $data = curl_exec( $ch );
        fclose( $fp );
        if ( $data === false )
        {
            echo "Error downloading ZIP file $zipUrl";
        }
        else
        {
            $resultUrl = "/ajax/downloadsubtitle/{$this->fileName}/" . rawurlencode( str_replace( '/', '#', $zipUrl ) ) . '/zip';

            $zip = new ZipArchive;
            $zip->open( $targetPath );
            for( $i = 0; $i < $zip->numFiles; $i++ )
            {
                $name = (string)$zip->getNameIndex( $i );

                $subType = substr( $name, strrpos( $name, '.' ) + 1 );

                $ret[] = array(
                    'name' => $name,
                    'link' => "{$resultUrl}/" . urlencode( str_replace( '/', '#', $name ) ),
                    'priority' => $this->computeSubtitlePriority( $name ),
                    /*'originSite' => $originSite */ );
            }
        }
        // remove temporary file
        @unlink( $targetPath );

        // no subtitles for this file
        if ( !count( $ret ) )
            return false;

        $this->filterList( $ret );

        // reverse sort by priority
        usort( $ret, function( $a, $b ) {
            if ( $a['priority'] == $b['priority'] ) return 0;
            return ( $a['priority'] < $b['priority'] ) ? 1 : -1;
        } );

        return $ret;
    }

    /**
     * Computes the priority for the subtitle file $subtitleName and origin website $originSite
     *
     * Criterias:
     * - language:
     *   - english = -20
     * - release group:
     *   - match = 7
     *   - no match = -7 (should always get negative prio)
     * - type:
     *   - ass = +3
     * - tag/notag:
     *   - tag = +1
     *   - notag = -1
     *
     * @param string $subtitleName
     * @param string $originSite tvsubtitles, usub, addic7ed, soustitres
     * @return integer
     */
    private function computeSubtitlePriority( $subtitleName )
    {
        $priority = 0;

        // english subs
        if ( preg_match( '#((\.VO[-\. ])|(en[\.-]((no)?tag\.)?(srt|ass))|(\.txt$))#i', $subtitleName ) )
            $priority -= 20;

        // release
        $priority += $this->release->matchesSubtitle( $subtitleName ) ? 7 :  -7;

        // type + tag/notag
        if ( substr( $subtitleName, strrpos( $subtitleName, '.' ) + 1 ) == 'ass' )
        {
            $priority += 3;
            if ( strstr( strtolower( $subtitleName ), '.tag' ) )
                $priority++;
            if ( strstr( strtolower( $subtitleName ), '.notag' ) )
                $priority--;
        }
        elseif ( strstr( strtolower( $subtitleName ), '.tag' ) )
            $priority++;
        if ( strstr( strtolower( $subtitleName ), '.notag' ) )
            $priority--;

        return $priority;
    }

    /**
     * Filters out unwanted subtitles based on similar files & priorities
     * - TAG over NoTaG
     * - ASS over SRT
     * - duplicates ? unsure... depends on version & origin
     *
     * @param array $list
     * @return void
     */
    private function filterList( &$list )
    {
        $assFiles = $srtFiles = array();
        $filesIndex = array();

        foreach( $list as $idx => $file )
        {
            $parts = pathinfo( $file['name'] );
            $filesIndex[$parts['basename']][] = $idx;
            if ( !isset( $parts['extension'] ) )
            {
                unset( $list[$idx] );
                continue;
            }
            if ( $parts['extension'] == 'srt' )
                $srtFiles[] = $parts['filename'];
            elseif ( $parts['extension'] == 'ass' )
                $assFiles[] = $parts['filename'];
        }
        foreach( array_intersect( $assFiles, $srtFiles ) as $duplicateFile )
        {
            $srtFile = "{$duplicateFile}.srt";
            foreach( $filesIndex[$srtFile] as $idx )
                unset( $list[$idx] );
        }
    }

    public $searchShowCode;
    public $searchSeason;
    public $searchEpisode;

    /**
     * The downloaded file
     * @param TVEpisodeDownloadedFile
     */
    private $release;

    protected $baseURL = 'http://www.sous-titres.eu/series/';

    private $aliases = array(
        'howimetyourmother' => 'himym',
        'thebigbangtheory' => 'bigbangtheory',
        'theitcrowd' => 'itcrowd',
        'thesimpsons' => 'simpsons',
        'mr_sunshine_2011' => 'mr_sunshine',
	'parenthood_2010' => 'parenthood',
    );
}
?>
