<?php
ini_set( 'include_path', ".:/usr/share/php");

if ( !defined('PHPUnit_MAIN_METHOD' ) )
{
    define('PHPUnit_MAIN_METHOD', 'MkvManagerScraperSubsynchroTest::main' );
}

require 'ezc/Base/ezc_bootstrap.php';

require_once 'lib/mkvmanager/interfaces/scraper.php';
require_once 'lib/mkvmanager/scraper_subsynchro.php';
require_once 'lib/mkvmanager/exceptions/scraper_html.php';

class MkvManagerScraperSubsynchroTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->scraper = new MkvManagerScraperSubsynchro();
        MkvManagerScraper::$isCacheEnabled = false;
    }

    public function testSearch()
    {
        $movies = $this->scraper->searchMovies( "kill bill" );

        $contents = array (
            array (
                'title' => 'Kill Bill : Volume 1 (2003)',
                'id' => '2003/kill-bill--volume-1.html',
                'info' => 'Kill Bill : Volume 1 - 3 releases - 3 fichiers',
            ),
            array (
                'title' => 'Kill Bill : Volume 2 (2003)',
                'id' => '2003/kill-bill--volume-2.html',
                'info' => 'Kill Bill : Volume 2 - 2 releases - 2 fichiers',
            ),
        );
        self::assertEquals( $contents, $movies );
    }

    public function testSearchNoResults()
    {
        $movies = $this->scraper->searchMovies( "azerty12345" );

        self::assertFalse( $movies );
    }

    public function testReleasesList()
    {
        $releases = $this->scraper->releasesList( "2003/kill-bill--volume-1.html" );

        $contents = array (
            array (
                'title' => 'Kill.Bill.2003.720p.Bluray.x264-SEPTiC',
                'id' => '2003/kill-bill--volume-1/kill-bill-2003-720p-bluray-x264-septic.html',
                'files' => '1',
            ),
            array (
                'title' => 'Kill.Bill.Vol.1.2003.720p.BluRay.DTS.x264-ESiR',
                'id' => '2003/kill-bill--volume-1/kill-bill-vol-1-2003-720p-bluray-dts-x264-esir.html',
                'files' => '1',
            ),
            array (
                'title' => 'Kill.Bill.Volume1.RETAIL.DVDRip.XviD-DiAMOND',
                'id' => '2003/kill-bill--volume-1/kill-bill-volume1-retail-dvdrip-xvid-diamond.html',
                'files' => '1',
            ),
        );
        self::assertEquals( $contents, $releases );
    }

    public function testGetReleaseSubtitles()
    {
        $subtitles = $this->scraper->getReleaseSubtitles( "2003/kill-bill--volume-1/kill-bill-2003-720p-bluray-x264-septic.html" );

        $contents = array( '2003/kill-bill--volume-1/kill-bill-2003-720p-bluray-x264-septic/fichier-3914.html' );

        self::assertEquals( $contents, $subtitles );
    }

    public function testDownloadSubtitle()
    {
        $path = $this->scraper->downloadSubtitle( "2001/monstres--cie/monsters-inc-2001-bdrip-h264-5-1ch-secretmyth/fichier-5584.html" );
        self::assertEquals( '2899b6063dfcc8bccc7d5c0d28287867', md5_file( $path ) );
    }

    private $scraper;
}
?>