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

        // brief test
        $scraper = new MkvManagerScraperBetaSeries( $this->VideoFile );
        $subtitles = $scraper->get();
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

        $fileUrl = 'http://www.betaseries.com/srt/' . $this->SubFileId;

        // subtitle save path
        $targetPath = '/home/download/downloads/complete/TV/Sorted/';
        preg_match( '/^((.*) - [0-9]+x[0-9]+ - (.*))\.(avi|mkv)$/', $this->VideoFile, $matches );

        // add show name folder and check for existence
        $targetPath .= $matches[2];
        if ( !file_exists( $targetPath ) )
            throw new Exception("Unable to locate folder $targetPath" );

        // add episode . subextension
        $targetPath .= "/{$matches[1]}.{$this->SubType}";

        // zip file: open as temporary
        if ( isset( $this->ZipFileId ) )
        {
            $ZipFileId = str_replace( '#', '/', $this->ZipFileId );

            $temporaryPath = '/tmp/' . md5( $this->VideoFile );

            $fp = fopen( $temporaryPath, 'wb' );
            $ch = curl_init( $fileUrl );
            curl_setopt( $ch, CURLOPT_URL, $fileUrl );
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
            $inputStream = $zip->getStream( $ZipFileId );
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
            $ch = curl_init( $fileUrl );
            curl_setopt( $ch, CURLOPT_URL, $fileUrl );
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

    public function doMergeQueueStatus()
    {

        $result = new ezcMvcResult();

        $session = ezcPersistentSessionInstance::get();
        $q = $session->createFindQuery( 'mmMergeOperation' );
        $q->where( $q->expr->in( 'status', mmMergeOperation::STATUS_PENDING, mmMergeOperation::STATUS_RUNNING ) )
          ->orderBy( 'create_time', 'asc' );
        $operations = $session->find( $q, 'mmMergeOperation' );
        $result->variables['operations'] = $operations;

        $htmlTable = '';
        foreach( $operations as $operation )
        {
            $htmlTable .=
                "<tr class=\"status\">" .
                "<td>{$operation->hash}</td>".
                "<td>".basename( $operation->targetFile )."</td>".
                "<td>{$operation->createTime}</td>".
                "<td>{$operation->endTime}</td>".
                "<td><progress id=\"progressBar\" value=\"".$operation->progress()."\" max=\"100\"></progress><span class=\"percent\">".$operation->progress()."%</span></td>".
                "</tr>";
        }
        $result->variables['html_table'] = $htmlTable;
        return $result;
    }
}
?>
