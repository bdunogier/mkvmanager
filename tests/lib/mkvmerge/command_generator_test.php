<?php
ini_set( 'include_path', ".:/usr/share/php");

if ( !defined('PHPUnit_MAIN_METHOD' ) )
{
    define('PHPUnit_MAIN_METHOD', 'MKVMergeCommandGeneratorTest::main' );
}

require_once 'lib/mkvmerge/command_generator.php';
require_once 'lib/mkvmerge/command_track_set.php';
require_once 'lib/mkvmerge/command_track.php';
require_once 'lib/mkvmerge/command_track_video.php';
require_once 'lib/mkvmerge/command_track_audio.php';
require_once 'lib/mkvmerge/command_track_subtitle.php';
require_once 'lib/mkvmerge/input_file.php';
require_once 'lib/mkvmerge/input_file_media.php';
require_once 'lib/mkvmerge/media_analyzer.php';
require_once 'lib/mkvmerge/command.php';

class MKVMergeCommandGeneratorTest extends PHPUnit_Framework_TestCase
{
    function testGenerateFromAVI()
    {
        $generator = new MKVMergeCommandGenerator();
        foreach( $generator->addInputFile( new MKVMergeMediaInputFile( 'tmp/tests/test.avi' ) ) as $commandTrack )
        {
            $commandTrack->language = 'eng';
        }

        foreach( $generator->tracks as $track )
        {
            self::assertType( 'MKVMergeCommandTrack', $track );
            self::assertEquals( 'eng', $track->language );
        }

        $generator->setOutputFile( 'tmp/tests/generated.mkv' );
        $command = $generator->get();
        print_r( $command );
    }
}
?>