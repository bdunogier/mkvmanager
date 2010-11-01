<?php
class MkvManagerScraperBetaSeries extends MkvManagerScraper
{
    /**
     * Constructor
     *
     * @param string $searchCriteria
     *        One of:
     *        - TV Show filename (<series> - <season>x<episode>...)
     *        - BetaSeries ID: theitcrowd, southpark...
     *
     * @todo Improve criteria to a named struct so that the scraper behaviour can
     *       be changed depending on the info we have
     */
    public function __construct( $searchCriteria )
    {
        // space: we have a human readable series name
        if ( strpos( $searchCriteria, ' ' ) !== false )
        {
            if ( !preg_match( '/^(.*) - ([0-9]+)x([0-9]+)/', $searchCriteria, $matches ) )
                throw new Exception( "Unable to use search criteria $searchCriteria" );
            $this->searchShow = $matches[1];
            $this->searchShowCode = strtolower( str_replace( array( ' ', '(', ')', '\'' ), '', $matches[1] ) );

            // aliases
            if ( isset( $this->aliases[$this->searchShowCode] ) )
                $this->searchShowCode = $this->aliases[$this->searchShowCode];

            $this->fileName = $searchCriteria;
            $this->searchSeason = $matches[2];
            $this->searchEpisode = $matches[3];

            $this->params['url'] = $this->searchShowCode;
            $this->params['saison'] = $this->searchSeason;
        }
    }

    public function get()
    {
        try {
            $doc = $this->fetch();
        } catch ( MkvManagerScraperHTMLException $e ) {
            throw $e;
        }

        $episodeId = sprintf( 'planning_srtS%02dE%02d', $this->searchSeason, $this->searchEpisode );
        //$episodeId = sprintf( '%sS%02de%02d', $this->searchShowCode, $this->searchSeason, $this->searchEpisode );

        $ret = array();
        list( $xp ) = $doc->xpath( '//div[@id="'.$episodeId.'"]' );
        foreach( $xp->div->ul->li as $li )
        {
            $item = $li[0]->children();

            $url = (string)$item[0]['href'];
            list( ,,,,$subtitleId ) = explode( '/', $url );
            $subtitleName = (string)$item[0];
            $subtitleLink = "/ajax/downloadsubtitle/" . rawurlencode( $this->fileName ) . "/{$subtitleId}";

            // if the file is a zip, we need to download it and read its contents
            if ( substr( $subtitleName, -4 ) == '.zip' )
            {
                // download to temporary folder
                $targetPath = '/tmp/' . md5( $subtitleName ) . '.zip';
                $fp = fopen( $targetPath, 'wb' );
                $ch = curl_init( $url );
                curl_setopt( $ch, CURLOPT_URL, $url );
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
                    // file_put_contents( $targetPath, $data );
                    $zip = new ZipArchive;
                    $zip->open( $targetPath );
                    for( $i = 0; $i < $zip->numFiles; $i++ )
                    {
                        $name = (string)$zip->getNameIndex( $i );
                        $subType = substr( $name, strrpos( $name, '.' ) + 1 );
                        $ret[] = array( 'name' => $name, 'link' => "{$subtitleLink}/{$subType}/" . urlencode( $name ) );
                    }
                }
                // remove temporary file
                @unlink( $targetPath );
            }
            else
            {
                // add sub  type (srt, ass)
                $subType = substr( $subtitleName, strrpos( $subtitleName, '.' ) + 1 );
                $ret[] = array( 'link' => "{$subtitleLink}/{$subType}", 'name' => $subtitleName );
            }
        }

        return $ret;
    }

    private $searchShowCode;
    private $searchSeason;
    private $searchEpisode;

    protected $baseURL = 'http://www.betaseries.com/ajax/planning_serie.php';

    private $aliases = array(
        'howimetyourmother' => 'himym',
        'thebigbangtheory' => 'bigbangtheory',
        'theitcrowd' => 'itcrowd',
        'thesimpsons' => 'simpsons',
    );
}
?>