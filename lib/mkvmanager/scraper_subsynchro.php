<?php
/**
 * Scraper subsynchro
 * 1. recherche par chaîne
 *    http://www.subsynchro.com/include/ajax/listeFilm.php?1=1&titre=kill+bill )
 *    => liste de résultats format XML
 *    <code>
 *    <results>
 *      <rs id="2003/kill-bill--volume-1.html" info="Kill Bill : Volume 1 - 3 releases - 3 fichiers">Kill Bill : Volume 1 (2003)</rs>
 *      <rs id="2003/kill-bill--volume-2.html" info="Kill Bill : Volume 2 - 2 releases - 2 fichiers">Kill Bill : Volume 2 (2003)</rs>
 *    </results>
 *    </code>
 * - recherche par ID resultat
 *   http://www.subsynchro.com/2003/kill-bill--volume-1.html
 *   => page HTML
 *   //ul[@class='liste_release']/a[not(@class)]
 *   => href=id page sous titre (2003/kill-bill--volume-1/kill-bill-2003-720p-bluray-x264-septic/kill-bill-2003-720p-bluray-x264-septic.html)
 * - téléchargement fichier
 *   http://www.subsynchro.com/2003/kill-bill--volume-1/kill-bill-2003-720p-bluray-x264-septic/kill-bill-2003-720p-bluray-x264-septic.html
 *   => page HTML
 *   //ul[@class='lien']/a
 *   => fichier zip
 */
class MkvManagerScraperSubsynchro extends MkvManagerScraper
{
    /**
     * Returns the list of movies for the search string $query
     * @param string $query
     * @return array(array('id', 'title', 'info')
     */
    public function searchMovies( $query )
    {
        $this->params['1'] = '1';
        $this->params['titre'] = $query;

        $doc = $this->fetch( $this->searchURL, 'parseFromXMLToXML' );
        $results = array();
        foreach( $doc->rs as $resultNode )
        {
            $results[] = array(
                'title' => (string)$resultNode,
                'id' => str_replace( '.html', '', (string)$resultNode['id'] ),
                'info' => (string)$resultNode['info'],
            );
        }

        return $results;
    }

    /**
     * Returns the list of releases for the movie ID $id
     * @param string $id
     * @return array(array('title', 'id'))
     */
    public function releasesList( $id )
    {
        $url = "{$this->siteURL}/{$id}.html";
        $doc = $this->fetch( $url );
        $resultNodes = $doc->xpath( "//ul[@class='liste_release']/descendant::a[not(@class)]" );
        $results = array();
        foreach( $resultNodes as $resultNode )
        {
            $release = array(
                'title' => (string)$resultNode,
                'id' => str_replace( '.html', '', (string)$resultNode['href'] ),
            );
            $results[] = $release;
        }

        return $results;
    }

    /**
     * Returns the list of subtitles for the release url $id
     * @param string $id
     */

    public function getReleaseSubtitles( $id )
    {
        $url = "{$this->siteURL}/{$id}.html";
        $doc = $this->fetch( $url );

        $results = array();
        foreach( $doc->xpath( "//li[@class='lien']/a" ) as $downloadNode )
        {
            $results[] = str_replace( 'http://www.subsynchro.com/', '', (string)$downloadNode['href'] );
        }

        return $results;
    }

    /**
     * Downloads the subtitlefile with id $id to $targetPath or a temporary path
     *
     * @param string $id Subtitle download URI part, from getReleaseSubtitles()
     * @param string $targetPath Target subtitle path, without the extension
     *
     * @return The path where the subtitle file was saved, or false if an error occured
     */
    public function downloadSubtitle( $id, $targetPath = null )
    {
        $downloadPageUrl = "{$this->siteURL}/{$id}";
        $downloadDoc = $this->fetch( $downloadPageUrl );
        $downloadNode = $downloadDoc->xpath( "//a[@id='telecharger']" );
        $downloadFileUri = (string)$downloadNode[0]['href'];

        $downloadFileUrl = "{$this->siteURL}/{$downloadFileUri}";

        $zipPath = '/tmp/' . md5( $downloadFileUrl ) . '.zip';
        if ( !$targetPath )
        {
            $targetPath = '/tmp/' . md5( $downloadFileUrl );
        }

        if ( !is_writable( dirname( $targetPath ) ) )
            throw new ezcBaseFilePermissionException( $targetPath );

        $fp = fopen( $zipPath, 'wb' );
        $ch = curl_init( $downloadFileUrl );
        curl_setopt( $ch, CURLOPT_URL, $downloadFileUrl );
        curl_setopt( $ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.10 (KHTML, like Gecko) Chrome/8.0.552.18 Safari/534.10' );
        curl_setopt( $ch, CURLOPT_REFERER, $this->siteURL );
        curl_setopt( $ch, CURLOPT_FILE, $fp );
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
        $data = curl_exec( $ch );
        fclose( $fp );
        $newTargetPath = false;
        if ( $data === false )
        {
            // @todo Add some kind of logging
        }
        else
        {
            $zip = new ZipArchive;
            $zip->open( $zipPath );
            for( $i = 0; $i < $zip->numFiles; $i++ )
            {
                $name = (string)$zip->getNameIndex( $i );
                $extension = pathinfo( $name, PATHINFO_EXTENSION );
                if ( $extension == 'srt' or $extension == 'ass' )
                {
                    $inputStream = $zip->getStream( $name );
                    $outputStream = fopen( $targetPath, 'w' );
                    stream_copy_to_stream( $inputStream, $outputStream );
                    fclose( $inputStream );
                    fclose( $outputStream );
                    $newTargetPath = "{$targetPath}.{$extension}";
                    rename( $targetPath, $newTargetPath );
                    break;
                }
            }
            $zip->close();
        }
        @unlink( $zipPath );

        return $newTargetPath;
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
        list( $xp ) = $doc->xpath( '//div[@id="'.$episodeId.'"]' );

        // no subtitles for this file
        if ( count( $xp->div->ul[0]->children() ) === 0 )
            return false;

        foreach( $xp->div->ul->li as $li )
        {
            $item = $li[0]->children();

            $url = (string)$item[0]['href'];
            $downloadURL = "{$this->siteURL}{$url}";
            $encodedDownloadURL = rawurlencode( str_replace( '/', '#', $downloadURL ) );
            list( ,, $subtitleId ) = explode( '/', $url );
            $subtitleName = (string)$item[0];

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

    /**
     * The downloaded file
     * @param TVEpisodeDownloadedFile
     */
    private $release;

    protected $siteURL = 'http://subsynchro.com';
    protected $searchURL = "http://subsynchro.com/include/ajax/listeFilm.php";
}
?>