<?php
class mmMkvManagerController extends ezcMvcController
{
    public function doDefault()
    {
        return new ezcMvcResult;
    }

    public function doFatal()
    {
        $result = new ezcMvcResult;
        $result->variables['exception'] = isset( $this->request->variables['exception'] );
        $result->variables['page_title'] = "An error has occured";
        return $result;
    }

    public function doTest()
    {
        echo "HEY";
        return new ezcMvcResult();
    }

    public function doMkvMerge()
    {
        $result = new ezcMvcResult;
        $result->variables['page_title'] = "MKV Merge command manager :: MKV Manager";
        $result->variables['targetDisks'] = mmMkvManagerDiskHelper::diskList();
        if ( isset( $_POST['WinCmd'] ) )
            $result->variables += mmApp::doConvertWinCMD( $_POST['WinCmd'], $_POST['Target'] );
        return $result;
    }

    /**
    * Controller method for the subtitles route
    * @return ezcMvcResult
    */
    public static function doSubtitles()
    {
        $result = new ezcMvcResult;
        $result->variables['page_title'] = "Subtitles manager :: MKV Manager";
        $result->variables += mmApp::doSubtitles();
        return $result;
    }

    /**
     * tvshow/image callback. Displays one of the show's image files, as found in
     * the Sorted folder
     *
     * @param string $image Image filename (fanart.jpg, folder.jpg...)
     *
     * @todo Catch the result by a) logging b) throwing a 404
     * @return ezcMvcResult
     */
    public function doTVShowImage()
    {
        $result = new ezcMvcResult;

        if ( $this->image == '' )
            throw new ezcBaseValueException( 'image', $this->image );

        $tvShowPath = ezcConfigurationManager::getInstance()->getSetting( 'tv', 'GeneralSettings', 'SourcePath' );
        $file = "{$tvShowPath}/" . str_replace( ':', '/', $this->image );
        // @todo Throw a dedicated extension
        if ( !file_exists( $file ) )
            throw new ezcBaseValueException( 'image', $file );

        $finfo = new finfo( FILEINFO_MIME );
        header( "Content-Type: " . $finfo->file( $file ) );
        readfile( $file );
        exit;

        return $result;
    }

    /**
     * Lists movies with no NFO files
     */
    public function doMoviesWithoutNFO()
    {
        $result = new ezcMvcResult();
        $result->variables['page_title'] = "Missing NFOs tracker :: MKV Manager";
        $result->variables += mmApp::doMoviesWithoutNFO();
        return $result;
    }

    /**
     * Shows the merge queue items
     */
    public function doMergeQueue()
    {
        $result = new ezcMvcResult();
        $result->variables['page_title'] = 'Merge queue status :: MKV Manager';
        $result->variables['items'] = $this->items;

        switch( $this->items )
        {
            case 'active':
                $statuses = array( mmMergeOperation::STATUS_PENDING, mmMergeOperation::STATUS_RUNNING );
                break;
            case 'archive':
                $statuses = array( mmMergeOperation::STATUS_ARCHIVED );
                break;
            case 'done':
                $statuses = array( mmMergeOperation::STATUS_DONE );
                break;
            case 'error':
                $statuses = array( mmMergeOperation::STATUS_ERROR );
                break;
            default:
                throw new ezcBaseFileNotFoundException( $this->items );
        }
        $session = ezcPersistentSessionInstance::get();
        $q = $session->createFindQuery( 'mmMergeOperation' );
        $q->where( $q->expr->in( 'status', $statuses ) )
          ->orderBy( 'create_time', 'asc' );
        $operations = $session->find( $q, 'mmMergeOperation' );
        $result->variables['operations'] = $operations;

        $htmlTable = '';
        $operationStructs = array();
        foreach( $operations as $hash => $operation )
        {
            $htmlTable .=
                "<tr class=\"status\">" .
                "<td>{$operation->hash}</td>".
                "<td>".basename( $operation->targetFile )."</td>".
                "<td>{$operation->createTime}</td>".
                "<td>{$operation->endTime}</td>".
                "<td><progress id=\"progressBar\" value=\"".$operation->progress()."\" max=\"100\"></progress><span class=\"percent\">".$operation->progress()."%</span></td>".
                "</tr>";
            $operationStructs[$hash] = $operation->asStruct();
        }
        $result->variables['html_table'] = $htmlTable;
        $result->variables['operations'] = $operationStructs;

        return $result;
    }

    /**
     * TV Shows dashboard
     */
    public function doTVDashBoard()
    {
        $result = new ezcMvcResult;
        $result->variables['page_title'] = "TV Dashboard :: MKV Manager";
        $result->variables += mmApp::doTVDashboard();
        return $result;
    }

    /**
     * List movies waiting for merge
     */
    public function doMovies()
    {
        $result = new ezcMvcResult;
        $result->variables['page_title'] = "Movies :: MKV Manager";
        $result->variables += mmApp::doMovies();
        return $result;
    }

    /**
     * Merge interface for movies
     * @param string $this->Folder The name of the movie folder
     */
    public function doMovieMerge()
    {
        $moviesPath = ezcConfigurationManager::getInstance()->getSetting( 'movies', 'GeneralSettings', 'SourcePath' );
        $result = new ezcMvcResult;
        $result->variables['page_title'] = "{$this->Folder} :: Movies :: MKV Manager";
        $result->variables['movie'] = $this->Folder;

        $generator = new MKVMergeCommandGenerator();

        // video file, mandatory
        $videoFiles = glob( "{$moviesPath}/{$this->Folder}/{$this->Folder}.{mkv,avi}", GLOB_BRACE );
        if ( !count( $videoFiles ) )
        {
            throw new InvalidArgumentException( "No files found matching pattern {$moviesPath}/{$this->Folder}/{$this->Folder}.{mkv,avi}" );
        }
        $videoFiles = $videoFiles[0];
        $generator->addInputFile( new MKVMergeMediaInputFile( $videoFiles ) );

        // subtitle file(s), optional
        $subtitlesFiles = glob( "{$moviesPath}/{$this->Folder}/{$this->Folder}*.{srt,avi}", GLOB_BRACE );
        if ( count( $subtitlesFiles ) )
        {
        }
        foreach( $subtitlesFiles as $subtitlesFile )
        {
            $generator->addInputFile( new MKVMergeSubtitleInputFile( $subtitlesFile, 'und' ) );
        }

        $tracks = array();
        foreach ( $generator->trackSets as $trackSet )
        {
            foreach ( $trackSet as $track )
            {
                $tracks[] = $track;
            }
        }

        // output file
        $generator->setOutputFile( "/media/storage/VIMES/Movies/{$this->Folder}/{$this->Folder}.mkv" );

        $result->variables['tracks'] = $tracks;
        $result->variables['command'] = $generator->getCommandString();

        $result->variables += mmApp::doMovies();
        return $result;
    }
}
?>
