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

    function testFromAnalysisResultAudio()
    {
        $inputFile = new MKVMergeMediaInputFile( 'tmp/tests/test.avi');
        $analysisResult = new stdClass;
        $analysisResult->index = 0;
        $analysisResult->type = 'audio';
        $analysisResult->language = 'eng';

        $track = MKVmergeCommandTrack::fromAnalysisResult( $analysisResult, $inputFile );
        self::assertType( 'MKVMergeCommandAudioTrack', $track );
        self::assertEquals( 'eng', $track->language );
    }

    function testFromAnalysisResultSubtitle()
    {
        $inputFile = new MKVMergeMediaInputFile( 'tmp/tests/test.avi');
        $analysisResult = new stdClass;
        $analysisResult->index = 0;
        $analysisResult->type = 'subtitles';
        $analysisResult->language = 'fre';

        $track = MKVmergeCommandTrack::fromAnalysisResult( $analysisResult, $inputFile );
        self::assertType( 'MKVMergeCommandSubtitleTrack', $track );
        self::assertEquals( 'fre', $track->language );
    }

    function testFromAnalysisResultMKVVideo()
    {
        $inputFile = new MKVMergeMediaInputFile( 'tmp/tests/test.mkv');
        $analysisResult = new stdClass;
        $analysisResult->index = 0;
        $analysisResult->type = 'video';
        $analysisResult->language = 'eng';
        $analysisResult->display_dimensions = '160x67';
        $analysisResult->default_track = 1;
        $analysisResult->forced_track = 0;

        $track = MKVmergeCommandTrack::fromAnalysisResult( $analysisResult, $inputFile );
        self::assertType( 'MKVMergeCommandVideoTrack', $track );
        self::assertEquals( 'eng', $track->language );
        self::assertEquals( false, $track->forced_track );
        self::assertEquals( true, $track->default_track );
    }
}
?>