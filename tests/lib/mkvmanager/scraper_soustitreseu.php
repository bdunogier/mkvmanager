<?php
ini_set( 'include_path', ".:/usr/share/php");

if ( !defined('PHPUnit_MAIN_METHOD' ) )
{
    define('PHPUnit_MAIN_METHOD', 'MkvManagerScraperSoustitreseuTest::main' );
}

require 'ezc/Base/ezc_bootstrap.php';

require_once 'lib/mkvmanager/interfaces/scraper.php';
require_once 'lib/mkvmanager/scraper_soustitreseu.php';
require_once 'lib/mkvmanager/tv_episode_file.php';
require_once 'lib/mkvmanager/tv_episode_downloaded_file.php';

class MkvManagerScraperSoustitreseuTest extends PHPUnit_Framework_TestCase
{
    public function testScrap()
    {
        $scraper = new MkvManagerScraperSoustitreseu( "Fringe - 3x19 - Lysergic Acid Diethylamide.mkv", "Fringe.S03E19.720p.HDTV.X264-DIMENSION.mkv" );
        MkvManagerScraper::$isCacheEnabled = false;
        $subtitles = $scraper->get();
        print_r( $subtitles );
    }
}
?>