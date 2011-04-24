<?php
class mmAjaxController extends ezcMvcController
{
    /**
     * Converts the windows MKVMerge command to a UNIX one and returns it
     */
    public function doMkvMerge()
    {
        $result = new ezcMvcResult;

        $result->variables['targetDisks'] = mmMkvManagerDiskHelper::diskList();

        if ( isset( $_POST['WinCmd'] ) )
            $winCmd = $_POST['WinCmd'];
        if ( isset( $_POST['Target'] ) )
            $targetDisk = $_POST['Target'];

        if ( isset( $winCmd, $targetDisk ) )
        {
            $result->variables = mmApp::doConvertWinCMD( $winCmd, $targetDisk );
        }

        return $result;
    }

    public function doFatal()
    {
        $result = new ezcMvcResult;
        $result->variables = $this->request->variables;
        return $result;
    }

    /**
     * Based on a windows command, returns the disk where the target file fits best
     */
    public function doBestFit()
    {
        $result = new ezcMvcResult;

        if ( isset( $_POST['WinCmd'] ) )
            $command = $_POST['WinCmd'];

        if ( isset( $command ) )
        {
            $result->variables = mmApp::doBestFit( $command );
        }

        return $result;
    }

    public function doSearchSubtitles()
    {
        $result = new ezcMvcResult;

        $release = isset( $this->release ) ? $this->release : null;
        $subtitles = array();
        foreach( array( 'MkvManagerScraperBetaSeries', 'MkvManagerScraperSoustitreseu' ) as $scraperClass )
        {
            $scraper = new $scraperClass( $this->VideoFile, $release );
            $scrapResult = $scraper->get();

            if ( $scrapResult != false )
            {
                $subtitles = array_merge( $subtitles, $scrapResult );
            }
        }

        usort( $subtitles, function( $a, $b ) {
            if ( $a['priority'] == $b['priority'] ) return 0;
            return ( $a['priority'] < $b['priority'] ) ? 1 : -1;
        } );

        if ( $subtitles === false )
        {
            $variables = array( 'status' => 'ko', 'message' => 'nosubtitles' );
        }
        else
        {
            $variables = array( 'status' => 'ok', 'subtitles' => $subtitles );
        }
        $result->variables = $variables;
        return $result;
    }

    public function doDownloadSubtitles()
    {
        $result = new ezcMvcResult;

        $downloadUrl = str_replace( '#', '/', $this->DownloadUrl );

        // subtitle save path
        $targetPath = '/home/download/downloads/complete/TV/Sorted/';
        $episodeFile = new TVEpisodeFile( $this->VideoFile );

        // add show name folder and check for existence
        $targetPath .= $episodeFile->showName;
        if ( !file_exists( $targetPath ) )
            throw new Exception("Unable to locate folder $targetPath" );

        // add episode . subextension
        $targetPath .= "/{$episodeFile->fullname}." . pathinfo( $this->SubFileName, PATHINFO_EXTENSION );

        // zip file: open as temporary
        if ( isset( $this->Zip ) )
        {
            $zipFileId = str_replace( '#', '/', $this->SubFileName );

            $temporaryPath = '/tmp/' . md5( $this->VideoFile );

            $fp = fopen( $temporaryPath, 'wb' );
            $ch = curl_init( $downloadUrl );
            curl_setopt( $ch, CURLOPT_URL, $downloadUrl );
            curl_setopt( $ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.10 (KHTML, like Gecko) Chrome/8.0.552.18 Safari/534.10' );
            // curl_setopt( $ch, CURLOPT_REFERER, $this->baseURL );
            curl_setopt( $ch, CURLOPT_FILE, $fp );
            curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
            $data = curl_exec( $ch );
            $info = curl_getinfo( $ch );
            fclose( $fp );

            // open zip file and get requested subtitle
            $zip = new ZipArchive;
            $zip->open( $temporaryPath );
            $inputStream = $zip->getStream( $zipFileId );
            $outputStream = fopen( $targetPath, 'wb' );
            stream_copy_to_stream( $inputStream, $outputStream );
            fclose( $inputStream );
            fclose( $outputStream );
            unlink( $temporaryPath );

            $result->variables = array( 'status' => 'ok', 'path' => $targetPath );
        }
        // sub file: copy directly
        else
        {
            $fp = fopen( $targetPath, 'wb' );
            $ch = curl_init( $downloadUrl );
            curl_setopt( $ch, CURLOPT_URL, $downloadUrl );
            curl_setopt( $ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.10 (KHTML, like Gecko) Chrome/8.0.552.18 Safari/534.10' );
            // curl_setopt( $ch, CURLOPT_REFERER, $this->baseURL );
            curl_setopt( $ch, CURLOPT_FILE, $fp );
            curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
            $data = curl_exec( $ch );
            $info = curl_getinfo( $ch );
            fclose( $fp );

            $result->variables = array( 'status' => 'ok', 'path' => $targetPath );
        }

        return $result;
    }

    /**
     * Generates the MKVMerge command for a video file
     *
     * @param string $VideoFile
     * @return ezcMvcResult
     */
    public function doGenerateMergeCommand()
    {
        $result = new ezcMvcResult;
        $command = MKVMergeTVCommandGenerator::generate( $this->VideoFile );

        $result->variables['command'] = (string)$command->command;
        return $result;
    }

    /**
     * Shows a merge operation status:
     * - progress %
     *
     * @param string $mergeId The merge operation's id
     */
    public function doMergeStatus()
    {
        $result = new ezcMvcResult();
        $result->variables += mmApp::doMergeStatus( $this->mergeHash );
        return $result;
    }

    /**
     * Shows a merge operation status:
     * - progress %
     *
     * @param string $mergeId The merge operation's id
     */
    public function doMergeActiveStatus()
    {
        $result = new ezcMvcResult();

        $session = ezcPersistentSessionInstance::get();
        $q = $session->createFindQuery( 'mmMergeOperation' );
        $q->where( $q->expr->in( 'status', mmMergeOperation::STATUS_RUNNING ) )
          ->limit( 1 );
        $operations = $session->find( $q, 'mmMergeOperation' );
        if ( count( $operations ) == 0 )
        {
            $result->variables = array( 'result' => 'ok', 'message' => 'no-operation' );
        }
        else
        {
            $operation = array_pop( $operations );
            $result->variables += mmApp::doMergeStatus( $operation->hash );
        }

        return $result;
    }

    /**
     * Adds a command to the operation queue
     */
    public function doQueueCommand()
    {
        $result = new ezcMvcResult;
        $command = $_POST['MergeCommand'];

        try {
            $mergeOperation = mmMergeOperation::queue( $command );
        } catch( Exception $e ) {
            $result->variables['result'] = 'ko';
            $result->variables['message'] = $e->getMessage();
            $result->variables['d'] = $_POST['MergeCommand'];
            return $result;
        }

        $result->variables['status'] = 'ok';
        $result->variables['operation_hash'] = $mergeOperation->hash;
        $result->variables['target_size'] = $mergeOperation->targetFileSize;
        $result->variables['target_file'] = $mergeOperation->targetFile;
        $result->variables['command'] = $command;

        return $result;
    }

    public function doMergeQueue()
    {
        $result = new ezcMvcResult();

        switch( $this->items )
        {
            case 'active':
                $statuses = array( mmMergeOperation::STATUS_PENDING, mmMergeOperation::STATUS_RUNNING );
                break;
            case 'archived':
                $statuses = array( mmMergeOperation::STATUS_DONE, mmMergeOperation::STATUS_ERROR );
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

    public function doSourcefileArchive()
    {
        $result = new ezcMvcResult();
        $nonExistingFiles = array();
        $hash = $this->hash;

        if ( $queueItem = mmMergeOperation::fetchByHash( $hash ) )
        {
            $status = 'ok';
            $message = '';
            $removed = array();
            $command = $queueItem->commandObject;
            if ( $command->conversionType == 'tvshow' )
            {
                $files = array_merge( $command->VideoFiles, $command->SubtitleFiles );
                foreach( $files as $file )
                {
                    $extension = pathinfo( $file['pathname'], PATHINFO_EXTENSION );
                    if ( ( $extension == 'mkv' or $extension == 'avi' ) &&
                        file_exists( $file['pathname']) &&
                        filesize( $file['pathname'] ) == 0 )
                    {
                        $result->variables['status'] = 'ko';
                        $result->variables['message'] = 'already_archived';
                        return $result;
                    }
                    if ( !file_exists( $file['pathname'] ) )
                    {
                        $nonExistingFiles[] = $file;
                    }
                    else
                    {
                        if ( !isset( $dummyFile ) )
                            $dummyFile = $file['pathname'];
                        $removed[] = $file['pathname'];
                        unlink( $file['pathname'] );
                    }
                }
                touch( $dummyFile );
            }
            else
            {
                $mainFile = $command->VideoFiles[0]['pathname'];
                if ( file_exists( $mainFile ) )
                {
                    $directory = dirname( $mainFile );
                    $files[] = glob( "$directory/*" );
                    try {
                        ezcBaseFile::removeRecursive( $directory );
                        $removed = $files;
                    } catch( ezcBaseFilePermissionException $e ) {
                        $status = 'ko';
                        $message = $e->getMessage();
                    }
                }
            }

            if ( !empty( $nonExistingFiles ) )
                $result->variables['messages'] = 'Some files were not found, see [not_found_files]';
            if ( $status === 'ok' )
            {
                $queueItem->status = mmMergeOperation::STATUS_ARCHIVED;
                ezcPersistentSessionInstance::get()->update( $queueItem );
            }
            $result->variables['status'] = $status;
            $result->variables['removed_files'] = $removed;
            $result->variables['not_found_files'] = $nonExistingFiles;
            $result->variables['message'] = $message;
        }
        else
        {
            // @todo Handle with exception
            $result->variables['status'] = 'ko';
            $result->variables['message'] = "No operation with hash $hash";
        }
        return $result;
    }

    public function doTestSourcefileArchive()
    {
        $result = new ezcMvcResult();
        $nonExistingFiles = array();
        $hash = $this->hash;

        // OK
        $status = 'ok';
        $message = '';
        $removed = array();

        // KO: already archived
        $status = 'ko';
        $message = 'already_archived';

        // KO: permission denied
        $status = 'ko';
        $message = 'Error: permission denied';

        if ( !empty( $nonExistingFiles ) )
            $result->variables['messages'] = 'Some files were not found, see [not_found_files]';
        $result->variables['status'] = $status;
        $result->variables['removed_files'] = $removed;
        $result->variables['not_found_files'] = $nonExistingFiles;
        $result->variables['message'] = $message;
        return $result;
    }

    /**
     * Generates an MKVMerge command for the video file $videoFile
     * @param string $videoFile
     */
    public function doGenerateCommand()
    {
        $result = new ezcMvcResult();

        $videoFile = $this->VideoFile;

        $episodeFile = new TVEpisodeFile( $videoFile );
        if ( !$episodeFile->hasSubtitleFile )
        {
            $result->variables['status'] = 'ko';
            $result->variables['message'] = "No subtitles found for $videoFile";
        }
        else
        {
            $result->variables['status'] = 'ok';
            $commandGenerator = new MKVMergeCommandGenerator();

            // add audio + video in english, and disable existing subtitles
            foreach( $commandGenerator->addInputFile( new MKVMergeMediaInputFile( $episodeFile->path ) )
                as $track )
            {
                if ( $track instanceof MKVmergeCommandSubtitleTrack )
                {
                    $track->disabled = true;
                }
                else
                {
                    $track->language = 'eng';
                    $track->default_track = true;
                }
            }
            // add subtitle file
            foreach( $commandGenerator->addInputFile( new MKVMergeSubtitleInputFile( $episodeFile->subtitleFile, 'fre' ) )
                as $track )
            {
                $track->language = 'fre';
                $track->default_track = true;
            }

            // determine best disk
            $bestFit = mmMkvManagerDiskHelper::BestTVEpisodeFit( $episodeFile->fullname, $episodeFile->fileSize );
            if ( $bestFit['RecommendedDiskHasFreeSpace'] )
                $disk = $bestFit['RecommendedDisk'];
            else
                $disk = 'VIMES';

            $commandGenerator->setOutputFile( "/media/storage/{$disk}/TV Shows/{$episodeFile->showName}/{$episodeFile->filename}" );

            $commandObject = $commandGenerator->get();
            $commandObject->appendSymLink = true;

            $result->variables['command'] = $commandObject->asString();
        }
        return $result;
    }
}
?>
