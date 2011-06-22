<?php
/**
 * Application class
 *
 * @version $Id$
 * @copyright 2010
 */
class mmApp
{
    /**
     * Converts a windows CMD variable
     *
     * @param string $winCMD
     * @param string $target
     * @return MKVMergeCommand
     */
    public static function doConvertWinCMD( $winCmd, $target )
    {
        try {
            $command = MKVMergeCommandImportWindowsGUI::convert( $winCmd, $target );
        } catch ( Exception $e ) {
            $exceptionMessage = $e->getMessage();
            $callstack = $e->getTraceAsString();
            $message = <<<EOF
<p class="error">An exception has occured in <span class="filename">{$e->getFile()}:{$e->getLine()}</p>
<p class="error message">{$exceptionMessage}</p>
<pre class="error dump">{$callstack}</pre>
EOF;
            throw new Exception( $message );
        }

        // symlink
        $command->appendSymLink = true;
        $command->appendMessage = true;

        return $command->toStruct();
    }

    /**
     * Calculates the best fit for a command
     *
     * @param string $command Windows command line
     * @return array ezcMvcResult content, others... :)
     */
    public static function doBestFit( $command )
    {
        $command = MKVMergeCommandImportWindowsGUI::convert( $command, false );
        $return = array( 'size' => $command->TargetSize );

        if ( $command->conversionType === 'tvshow' )
        {
            $return += mmMkvManagerDiskHelper::BestTVEpisodeFit( $command->title, $command->TargetSize );
        }

        return $return;
    }

    /**
     * Disblayes the files requiring subtitles
     *
     * @return array
     */
    public static function doSubtitles()
    {
        $result = array();
        $result['VideoFiles'] = mmMkvManagerSubtitles::fetchFiles();

        return $result;
    }

    /**
     * Generates a merge operation status report
     *
     * @param string $mergeId The merge operation's id
     * @return mmMergeStatusReport
     */
    public static function doMergeStatus( $mergeHash )
    {
        $operation = mmMergeOperation::fetchByHash( $mergeHash );
        if ( !($operation instanceof mmMergeOperation ) )
        {
            $return['status'] = 'ko';
            $return['message'] = 'not_found';
        }
        else
        {
            $return['status'] = 'ok';
            $return['progress'] = $operation->progress();
            $return['file'] = $operation->commandObject->title;
        }
        return $return;
    }

    /**
     * Lists movies that have no NFO files
     *
     * @return array
     */
    public static function doMoviesWithoutNFO()
    {
        // callback that strips down a movie file path to the movie's path

        $moviesPath = ezcConfigurationManager::getInstance()->getSetting( 'movies', 'GeneralSettings', 'SourcePath' );
        // we need to know this since some of the next lines need it
        $moviesPathLength = strlen( $moviesPath );
        $slashElementCount = count( explode( "/", $moviesPath ) );

            /**
             * mmApp::doMovies()
             *
             * @return
             */
            $callback = function( &$value, $key, $params ) {
$value = substr( $value, 0, strpos( $value, '/', $params['movies_path_element_count'] ) );
};

        // list of movie files, extensions stripped
        $simpleMoviesFormat = glob( $moviesPath . '/*/*.{mkv,avi}', GLOB_BRACE );
        array_walk( $simpleMoviesFormat, $callback, array( 'movies_path_element_count' => $moviesPathLength+1 ));

        // list of movie folder having a bluray rip format
        $bdmvMoviesFormat = glob( $moviesPath . '/*/BDMV/index.bdmv', GLOB_BRACE );
        array_walk( $bdmvMoviesFormat, $callback, array( 'movies_path_element_count' => $moviesPathLength+1 ));

        $moviesFiles = array_merge( $bdmvMoviesFormat, $simpleMoviesFormat );

        // list of NFO files, extensions stripped
        $moviesNFOs  = glob( $moviesPath . '/*/*.nfo' );
        foreach( $moviesNFOs as $key => $filename )
        {
            $dom = new DOMDocument();
            if( ! @$dom->load( $filename ))
            {
                unset( $moviesNFOs[$key] );
            }
        }
        reset( $moviesNFOs );
        array_walk( $moviesNFOs, $callback, array( 'movies_path_element_count' => $moviesPathLength+1 ) );

        // the diff of both arrays gives us movies without NFOS (and NFOs without movies, but that's unlikely)
        $movies = array_diff( $moviesFiles, $moviesNFOs );

        // Transform the list to titles only
        array_walk( $movies, function( &$value, $key, $params ){
            $parts = explode( '/', $value );
            $movieElementIndex = $params['movies_path_element_count'];
            $value = $parts[$movieElementIndex];
        }, array( 'movies_path_element_count' => $slashElementCount ) );

        sort( $movies );

        return compact( 'movies' );
    }

    /**
     * mmApp::doTVDashboard()
     *
     * @return array
     */
    public static function doTVDashboard()
    {
        $tvShowPath = ezcConfigurationManager::getInstance()->getSetting( 'tv', 'GeneralSettings', 'SourcePath' );
        $shows = array();
        $byDate = array();
        $notValidEpisode = array();

        foreach( mmMkvManagerSubtitles::fetchFiles() as $file )
        {
            $episode = new TVEpisodeFile( $file );
            if( $episode->isValid )
            {
            $show = new TVShowFolder( $file, $tvShowPath );
            if (!isset( $queueFiles[$episode->showName] ) )
                $queueFiles[$episode->showName] = array();
            $shows[$episode->showName][] = $episode;
            $filemtime = filemtime( "{$tvShowPath}/{$episode->showName}/{$file}" );
            $byDate[$filemtime] = $episode;
            }
            else
            {
                $notValidEpisode[] = $episode;
            }
        }
        krsort( $byDate );
        $latest = array_slice( $byDate, 0, 3 );

        return array( 'shows' => $shows, 'latest' => $latest );
    }

    public static function doMovies()
    {
        $moviesPath = ezcConfigurationManager::getInstance()->getSetting( 'movies', 'GeneralSettings', 'SourcePath' );
    	$movieFolders = array();
        foreach( glob( "{$moviesPath}/*", GLOB_BRACE|GLOB_ONLYDIR ) as $movieFolder )
        {
            $movieFolders[] = basename( $movieFolder );
        }
        return array(
            'movies' => $movieFolders,
        );
    }

    /**
     * Fetches the NFO for the movie id $AllocineId
     *
     * @param string AllocineId
     * @return array(nfo => string))
     */
    public function doNfo( $allocineId )
    {
        $scraper = new MkvManagerScraperAllocine;
        $infos = $scraper->getMovieDetails( $allocineId );
        $writer = new \mm\Xbmc\Nfo\Writers\Movie( $infos );

        return array( 'nfo' => $writer->get() );
    }

    /**
     * Fetches the NFO for the movie id $AllocineId
     *
     * @param string AllocineId
     * @return array(nfo => string))
     */
    public function doSaveNfo( $allocineId, $movieFolder )
    {
        $scraper = new MkvManagerScraperAllocine;
        $infos = $scraper->getMovieDetails( $allocineId );
        $writer = new \mm\Xbmc\Nfo\Writers\Movie( $infos );

        $nfoPath = ezcConfigurationManager::getInstance()->getSetting( 'movies', 'GeneralSettings', 'SourcePath' ) .
            DIRECTORY_SEPARATOR . $movieFolder . DIRECTORY_SEPARATOR . "$movieFolder.nfo";

        return array( 'nfo' => $writer->write( $nfoPath ) );
    }
}
?>