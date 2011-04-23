<?php
ini_set( 'include_path', ".:/usr/share/php");

if ( !defined('PHPUnit_MAIN_METHOD' ) )
{
    define('PHPUnit_MAIN_METHOD', 'MKVMergeCommandGeneratorTest::main' );
}

require 'ezc/Base/ezc_bootstrap.php';

require_once 'lib/mkvmerge/command_generator.php';
require_once 'lib/mkvmerge/command_track_set.php';
require_once 'lib/mkvmerge/command_track.php';
require_once 'lib/mkvmerge/command_track_video.php';
require_once 'lib/mkvmerge/command_track_audio.php';
require_once 'lib/mkvmerge/command_track_subtitle.php';
require_once 'lib/mkvmerge/input_file.php';
require_once 'lib/mkvmerge/input_file_media.php';
require_once 'lib/mkvmerge/input_file_subtitle.php';
require_once 'lib/mkvmerge/media_analyzer.php';
require_once 'lib/mkvmerge/command.php';

class MKVMergeCommandGeneratorTest extends PHPUnit_Framework_TestCase
{
    function testGenerateFromAVI()
    {
        $expectedCommand =
            "mkvmerge " .
            "-o 'tmp/tests/generated.mkv' "  .
            "--language 0:eng --language 1:eng -a 1 -d 0 -S -T -M --no-global-tags 'tmp/tests/test.avi'";

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
        $command = $generator->getCommandString();

        self::assertEquals( $expectedCommand, $command );
    }

    function testGenerateFromAVIWithSub()
    {
        $expectedCommand =
            "mkvmerge " .
            "-o 'tmp/tests/generated.mkv' " .
            "--language 0:eng --language 1:eng -a 1 -d 0 -S -T -M --no-global-tags 'tmp/tests/test.avi' " .
            "--language 0:fre --sub-charset 0:ISO-8859-1 -A -D -s 0 -T -M --no-global-tags 'tmp/tests/test.ass'";

        $generator = new MKVMergeCommandGenerator();
        foreach( $generator->addInputFile( new MKVMergeMediaInputFile( 'tmp/tests/test.avi' ) ) as $commandTrack )
        {
            $commandTrack->language = 'eng';
        }
        $subtitlesTrack = $generator->addInputFile( new MKVMergeSubtitleInputFile( 'tmp/tests/test.ass', 'fre' ) );
        $subtitlesTrack[0]->language = 'fre';

        foreach( $generator->tracks as $track )
        {
            self::assertType( 'MKVMergeCommandTrack', $track );
            self::assertEquals( 'eng', $track->language );
        }

        $generator->setOutputFile( 'tmp/tests/generated.mkv' );
        $command = $generator->getCommandString();

        self::assertEquals( $expectedCommand, $command );
    }

    function testGenerateFromMKV()
    {
        $expectedCommand =
            "mkvmerge " .
            "-o 'tmp/tests/generated.mkv' " .
            "--language 1:eng --forced-track 1:no --default-track 1:yes " .
            "--language 2:eng --forced-track 2:no --default-track 2:yes " .
            "--language 3:eng --sub-charset 3:UTF-8 --forced-track 3:no --default-track 3:yes " .
            "-a 2 -d 1 -s 3 -T -M --no-global-tags 'tmp/tests/test.mkv' " .
            "--language 0:fre --sub-charset 0:ISO-8859-1 --default-track 0:yes ".
            "-A -D -s 0 -T -M --no-global-tags 'tmp/tests/test.ass'";

        $generator = new MKVMergeCommandGenerator();
        foreach( $generator->addInputFile( new MKVMergeMediaInputFile( 'tmp/tests/test.mkv' ) ) as $commandTrack )
        {
            $commandTrack->language = 'eng';
            $commandTrack->default_track = true;
        }
        $subtitlesTrack = $generator->addInputFile( new MKVMergeSubtitleInputFile( 'tmp/tests/test.ass', 'fre' ) );
        $subtitlesTrack[0]->language = 'fre';
        $subtitlesTrack[0]->default_track = true;

        foreach( $generator->tracks as $track )
        {
            self::assertType( 'MKVMergeCommandTrack', $track );
            self::assertEquals( 'eng', $track->language );
        }

        $generator->setOutputFile( 'tmp/tests/generated.mkv' );
        $command = $generator->getCommandString();

        self::assertEquals( $expectedCommand, $command );
    }

    public function testGenerateRealTVMKV()
    {
        self::markTestSkipped();

        $mkvFile = '/home/download/downloads/complete/TV/Sorted/Californication/Californication - 4x09 - Another Perfect Day.mkv';
        $assFile = '/home/download/downloads/complete/TV/Sorted/Californication/Californication - 4x09 - Another Perfect Day.ass';

        $generator = new MKVMergeCommandGenerator();

        foreach( $generator->addInputFile( new MKVMergeMediaInputFile( $mkvFile ) ) as $commandTrack )
        {
            $commandTrack->language = 'eng';
        }

        foreach( $generator->addInputFile( new MKVMergeMediaInputFile( $assFile ) ) as $commandTrack )
        {
            $commandTrack->language = 'fre';
        }

        $generator->setOutputFile( '/media/storage/CARROT/TV Shows/Californication/Californication - 4x09 - Another Perfect Day.mkv' );
        $command = $generator->getCommandString();

        // echo $command;
    }
}
?>