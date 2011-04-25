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


            /**
             * mmApp::doMovies()
             *
             * @return
             */
            $callback = function( &$value, $key ) {
$value = substr( $value, 0, strrpos( $value, '/', 5 ) );
};

        // list of movie files, extensions stripped
        $moviesFiles = glob( '/media/aggregateshares/Movies/*/*.{mkv,avi}', GLOB_BRACE );
        array_walk( $moviesFiles, $callback );

        // list of NFO files, extensions stripped
        $moviesNFOs  = glob( '/media/aggregateshares/Movies/*/*.nfo' );
        array_walk( $moviesNFOs, $callback );

        // the diff of both arrays gives us movies without NFOS (and NFOs without movies, but that's unlikely)
        $movies = array_diff( $moviesFiles, $moviesNFOs );

        // Transform the list to titles only
        array_walk( $movies, function( &$value, $key ){
            $parts = explode( '/', $value );
            $value = $parts[4];
        });

        return compact( 'movies' );
    }

    /**
     * mmApp::doTVDashboard()
     *
     * @return array
     */
    public static function doTVDashboard()
    {
        $shows = array();
        $byDate = array();
        foreach( mmMkvManagerSubtitles::fetchFiles() as $file )
        {
            $episode = new TVEpisodeFile( $file );
            $show = new TVShowFolder( $file, '/home/download/downloads/complete/TV/Sorted' );
            if (!isset( $queueFiles[$episode->showName] ) )
                $queueFiles[$episode->showName] = array();
            $shows[$episode->showName][] = $episode;
            $filemtime = filemtime( "/home/download/downloads/complete/TV/Sorted/{$episode->showName}/{$file}" );
            $byDate[$filemtime] = $episode;
        }
        krsort( $byDate );
        $latest = array_slice( $byDate, 0, 3 );

        return array( 'shows' => $shows, 'latest' => $latest );
    }

    public static function doMovies()
    {
        $movieFolders = array();
        foreach( glob( '/home/download/downloads/complete/Movies/*', GLOB_BRACE|GLOB_ONLYDIR ) as $movieFolder )
        {
            $movieFolders[] = basename( $movieFolder );
        }
        return array(
            'movies' => $movieFolders,
        );
    }
}
?>