<?php
ini_set( 'include_path', ".:/usr/share/php");

if ( !defined('PHPUnit_MAIN_METHOD' ) )
{
    define('PHPUnit_MAIN_METHOD', 'MKVMergeMediaAnalyzerTest::main' );
}

require 'ezc/Base/ezc_bootstrap.php';

require_once 'lib/mkvmerge/media_analyzer.php';
require_once 'lib/mkvmerge/input_file.php';
require_once 'lib/mkvmerge/input_file_media.php';

class MKVMergeMediaAnalyzerTest extends PHPUnit_Framework_TestCase
{
    function testAnalyzeAVI()
    {
        $analyzer = new MKVMergeMediaAnalyzer( new MKVMergeMediaInputFile( 'tmp/tests/test.avi' ) );
        $result = $analyzer->getResult();

        echo var_export( $result );

        self::assertEquals( 2, count( $result ) );

        self::assertType( 'stdClass', $result[0] );
        self::assertEquals( 0, $result[0]->index );
        self::assertEquals( 'video', $result[0]->type );
        self::assertObjectNotHasAttribute( 'language', $result[0] );

        self::assertType( 'stdClass', $result[1] );
        self::assertEquals( 1, $result[1]->index );
        self::assertEquals( 'audio', $result[1]->type );
        self::assertObjectNotHasAttribute( 'language', $result[1] );
    }

    function testAnalyzeMKV()
    {
        $analyzer = new MKVMergeMediaAnalyzer( new MKVMergeMediaInputFile( 'tmp/tests/test.mkv' ) );
        $result = $analyzer->getResult();

        self::assertEquals( 3, count( $result ) );

        self::assertType( 'stdClass', $result[1] );
        self::assertEquals( 1, $result[1]->index );
        self::assertEquals( 'video', $result[1]->type );
        self::assertEquals( 'eng', $result[1]->language );

        self::assertType( 'stdClass', $result[2] );
        self::assertEquals( 2, $result[2]->index );
        self::assertEquals( 'audio', $result[2]->type );
        self::assertEquals( 'und', $result[2]->language );

        self::assertType( 'stdClass', $result[3] );
        self::assertEquals( 3, $result[3]->index );
        self::assertEquals( 'subtitles', $result[3]->type );
        self::assertEquals( 'eng', $result[3]->language );
    }
}
?>