<?php
class MkvManagerScraperBetaSeries extends MkvManagerScraper
{
    /**
     * Constructs a new betaseries.com scraper
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
            if ( !preg_match( '/^(.*) - ([0-9]+)x([0-9]+)/', $searchCriteria, $matches ) )
                throw new Exception( "Unable to use search criteria $searchCriteria" );
            $this->searchShow = $matches[1];
            $this->searchShowCode = strtolower( str_replace( array( ' ', '(', ')', '\'', '.' ), '', $matches[1] ) );

            // aliases
            if ( isset( $this->aliases[$this->searchShowCode] ) )
                $this->searchShowCode = $this->aliases[$this->searchShowCode];

            $this->fileName = $searchCriteria;
            $this->searchSeason = $matches[2];
            $this->searchEpisode = $matches[3];

            $this->params['url'] = $this->searchShowCode;
            $this->params['saison'] = $this->searchSeason;

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

        $episodeId = sprintf( 'srt%sS%02dE%02d', $this->searchShowCode, $this->searchSeason, $this->searchEpisode );

        $ret = array();

        $liArray = $doc->xpath( sprintf( '//div[@id="srt%sS%02dE%02d"]//li', $this->searchShowCode, $this->searchSeason, $this->searchEpisode ) );
        if ( !count( $liArray ) )
            return false;

        foreach( $liArray as $li )
        {
            $link = $li->a;
            $downloadURL = (string)$link['href'];
            $encodedDownloadURL = rawurlencode( str_replace( '/', '#', $downloadURL ) );
            $subtitleId = array_pop( explode( '/', $downloadURL ) );
            $subtitleName = (string)$link;

            // class="<originSite> off/on"
            list( $originSite ) = explode( ' ', (string)$li['class'] );
            $subtitleLink = "/ajax/downloadsubtitle/" . rawurlencode($this->fileName) . "/{$encodedDownloadURL}/" . rawurlencode( $subtitleName );

            // if the file is a zip, we need to download it and read its contents
            if ( substr( $subtitleName, -4 ) == '.zip' )
            {
                // download to temporary folder
                $targetPath = '/tmp/' . md5( $subtitleName ) . '.zip';
                $fp = fopen( $targetPath, 'wb' );
                $ch = curl_init( $downloadURL );
                curl_setopt( $ch, CURLOPT_URL, $downloadURL );
                curl_setopt( $ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.10 (KHTML, like Gecko) Chrome/8.0.552.18 Safari/534.10' );
                curl_setopt( $ch, CURLOPT_REFERER, $this->baseURL );
                curl_setopt( $ch, CURLOPT_FILE, $fp );
                curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
                $data = curl_exec( $ch );
                fclose( $fp );
                if ( $data === false )
                {
                    // @todo Add some kind of logging
                }
                else
                {
                    $zip = new ZipArchive;
                    $zip->open( $targetPath );
                    for( $i = 0; $i < $zip->numFiles; $i++ )
                    {
                        $name = (string)$zip->getNameIndex( $i );

                        $subType = substr( $name, strrpos( $name, '.' ) + 1 );

                        $ret[] = array(
                            'name' => $name,
                            'link' => "{$subtitleLink}/" . rawurlencode( str_replace( '/', '#', $name ) ),
                            'priority' => $this->computeSubtitlePriority( $name, $originSite ),
                            'originSite' => $originSite  );
                    }
                }
                // remove temporary file
                @unlink( $targetPath );
            }
            else
            {
                // add sub type (srt, ass)
                $subType = substr( $subtitleName, strrpos( $subtitleName, '.' ) + 1 );
                $ret[] = array(
                    'link' => "{$subtitleLink}",
                    'name' => $subtitleName,
                    'priority' => $this->computeSubtitlePriority( $subtitleName, $originSite ),
                    'originSite' => $originSite );
            }
        }

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
     * - origin site:
     *   - soustitres: +1
     *   - usub: 0
     *   - addic7ed: -1
     *   - tvsubtitles: -2
     *
     * @param string $subtitleName
     * @param string $originSite tvsubtitles, usub, addic7ed, soustitres
     * @return integer
     */
    private function computeSubtitlePriority( $subtitleName, $originSite )
    {
        $priority = 0;

        // english subs
        if ( preg_match( '#((\.VO[-\. ])|(en\.((no)?tag\.)?(srt|ass))|(\.txt$))#i', $subtitleName ) )
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

        // origin site
        switch ( $originSite )
        {
            case 'soustitres':  $priority += 2; break;
            case 'addic7ed':    $priority -= 1; break;
            case 'tvsubtitles': $priority -= 2; break;
        }

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

    private $searchShowCode;
    private $searchSeason;
    private $searchEpisode;

    /**
     * The downloaded file
     * @param TVEpisodeDownloadedFile
     */
    private $release;

    protected $siteURL = 'http://www.betaseries.com/';
    protected $baseURL = "http://www.betaseries.com/ajax/episodes/season.php";

    private $aliases = array(
        'howimetyourmother' => 'himym',
        'thebigbangtheory' => 'bigbangtheory',
        'theitcrowd' => 'itcrowd',
        'thesimpsons' => 'simpsons',
        'mrsunshine2011' => 'mrsunshine',
        'breakingin' => 'breaking-in',
        'terranova' => 'terra-nova',
    );
}
?>
