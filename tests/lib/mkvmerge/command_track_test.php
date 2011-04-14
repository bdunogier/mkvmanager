<?php
ini_set( 'include_path', ".:/usr/share/php");

if ( !defined('PHPUnit_MAIN_METHOD' ) )
{
    define('PHPUnit_MAIN_METHOD', 'MKVMergeCommandTrack::main' );
}

require 'ezc/Base/ezc_bootstrap.php';

require_once 'lib/mkvmerge/command_track.php';
require_once 'lib/mkvmerge/command_track_audio.php';
require_once 'lib/mkvmerge/command_track_video.php';
require_once 'lib/mkvmerge/command_track_subtitle.php';
require_once 'lib/mkvmerge/input_file.php';
require_once 'lib/mkvmerge/input_file_media.php';
require_once 'lib/mkvmerge/input_file_subtitle.php';

class MKVMergeCommandTrackTest extends PHPUnit_Framework_TestCase
{
    function testFromAnalysisResultVideo()
    {
        $inputFile = new MKVMergeMediaInputFile( 'tmp/tests/test.avi');
        $analysisResult = new stdClass;
        $analysisResult->index = 0;
        $analysisResult->type = 'video';
        $analysisResult->language = 'eng';

        $track = MKVmergeCommandTrack::fromAnalysisResult( $analysisResult, $inputFile );
        self::assertType( 'MKVMergeCommandVideoTrack', $track );
        self::assertEquals( 'eng', $track->language );
    }
}
?>