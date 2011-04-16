<?php
ini_set( 'include_path', ".:/usr/share/php");

if ( !defined('PHPUnit_MAIN_METHOD' ) )
{
    define('PHPUnit_MAIN_METHOD', 'MKVMergeMediaAnalyzerTest::main' );
}

require 'ezc/Base/ezc_bootstrap.php';

require_once 'lib/mkvmerge/media_analyzer.php';
require_once 'lib/mkvmerge/command_track.php';
require_once 'lib/mkvmerge/command_track_set.php';
require_once 'lib/mkvmerge/command_track_video.php';
require_once 'lib/mkvmerge/command_track_audio.php';
require_once 'lib/mkvmerge/command_track_subtitle.php';
require_once 'lib/mkvmerge/input_file.php';
require_once 'lib/mkvmerge/input_file_media.php';

class MKVMergeMediaAnalyzerTest extends PHPUnit_Framework_TestCase
{
    function testAnalyzeAVI()
    {
        $analyzer = new MKVMergeMediaAnalyzer( new MKVMergeMediaInputFile( '/media/aggregateshares/TV Shows/Koh-Lanta/Koh-Lanta - 10x01.avi' ) );
        $analyzer->analyze();
        self::assertType( 'MKVMergeCommandTrackSet', $analyzer->trackSet );
        self::assertEquals( 2, count( $analyzer->trackSet ) );
        self::assertType( 'MKVMergeCommandVideoTrack', $analyzer->trackSet[0] );
        self::assertType( 'MKVMergeCommandAudioTrack', $analyzer->trackSet[1] );
    }

    function testAnalyzeMKV()
    {
        $analyzer = new MKVMergeMediaAnalyzer( new MKVMergeMediaInputFile( '/media/storage/STARBUCK/TV Shows/Fringe/Fringe - 1x01 -  Pilot.mkv' ) );
        $analyzer->analyze();
        self::assertType( 'MKVMergeCommandTrackSet', $analyzer->trackSet );
        self::assertEquals( 3, count( $analyzer->trackSet ) );
        self::assertType( 'MKVMergeCommandVideoTrack', $analyzer->trackSet[1] );
        self::assertType( 'MKVMergeCommandAudioTrack', $analyzer->trackSet[2] );
        self::assertType( 'MKVMergeCommandSubtitleTrack', $analyzer->trackSet[3] );
    }
}
?>