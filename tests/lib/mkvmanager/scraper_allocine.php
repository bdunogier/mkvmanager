<?php
ini_set( 'include_path', ".:/usr/share/php");

if ( !defined('PHPUnit_MAIN_METHOD' ) )
{
    define('PHPUnit_MAIN_METHOD', 'MkvManagerScraperSubsynchroTest::main' );
}

require 'ezc/Base/ezc_bootstrap.php';

require_once 'lib/mkvmanager/interfaces/scraper.php';
require_once 'lib/mkvmanager/scraper_allocine.php';
require_once 'lib/mkvmanager/scraper_allocine2.php';
require_once 'lib/mkvmanager/exceptions/scraper_html.php';
require_once 'lib/mkvmanager/exceptions/scraper_http.php';

require_once 'lib/Info/Movie/SearchResult.php';
require_once 'lib/Info/Movie/Details.php';
require_once 'lib/Info/Person.php';
require_once 'lib/Info/Actor.php';
require_once 'lib/Info/Trailer.php';
require_once 'lib/Info/Director.php';

class MkvManagerScraperAllocineTest extends PHPUnit_Framework_TestCase
{
    public function testSearch()
    {
        $scraper = new self::$class();
        MkvManagerScraper::$isCacheEnabled = false;
        $movies = $scraper->searchMovies( "lord of the rings" );

        $contents = array (
            MkvManagerScraperAllocineSearchResult::__set_state(array(
                'thumbnail' => 'http://images.allocine.fr/r_75_106/medias/nmedia/18/65/65/36/18880027.jpg',
                'originalTitle' => 'The Lord of the Rings',
                'title' => 'Le Seigneur des anneaux',
                'link' => '/film/fichefilm_gen_cfilm=38512.html',
                'allocineId' => 38512,
                'year' => 1978,
                'director' => 'Ralph Bakshi',
                'actors' => array( 'Christopher Guard', 'Michel Caccia' ),
            ) ),
            MkvManagerScraperAllocineSearchResult::__set_state(array(
                'thumbnail' => 'http://images.allocine.fr/r_75_106/medias/nmedia/18/35/14/33/18366630.jpg',
                'originalTitle' => 'The Lord of the Rings: The Return of the King',
                'title' => 'Le Seigneur des anneaux : le retour du roi',
                'link' => '/film/fichefilm_gen_cfilm=39187.html',
                'allocineId' => 39187,
                'year' => 2003,
                'director' => 'Peter Jackson',
                'actors' => array( 'Elijah Wood', 'Sean Astin' ),
            ) ),
            MkvManagerScraperAllocineSearchResult::__set_state(array(
                'thumbnail' => 'http://images.allocine.fr/r_75_106/medias/nmedia/00/02/54/95/affiche2.jpg',
                'originalTitle' => 'The Lord of the Rings: The Two Towers',
                'title' => 'Le Seigneur des anneaux : les deux tours',
                'link' => '/film/fichefilm_gen_cfilm=39186.html',
                'allocineId' => 39186,
                'year' => 2002,
                'director' => 'Peter Jackson',
                'actors' => array( 'Elijah Wood', 'Sean Astin' ),
            ) ),
            MkvManagerScraperAllocineSearchResult::__set_state(array(
                'thumbnail' => 'http://images.allocine.fr/r_75_106/medias/nmedia/00/02/16/27/69218096_af.jpg',
                'originalTitle' => 'The Lord of the Rings: The Fellowship of the Ring',
                'title' => 'Le Seigneur des anneaux : la communauté de l\'anneau',
                'link' => '/film/fichefilm_gen_cfilm=27070.html',
                'allocineId' => 27070,
                'year' => 2001,
                'director' => 'Peter Jackson',
                'actors' => array( 'Elijah Wood', 'Sean Astin' ),
            ) )
        );

        self::assertEquals( $contents, $movies );
    }

    public function testSearchNoMoviesResults()
    {
        $scraper = new self::$class();
        MkvManagerScraper::$isCacheEnabled = false;
        $movies = $scraper->searchMovies( "foobar" );

        self::assertFalse( $movies );
    }

    public function testSearchNoResults()
    {
        $scraper = new self::$class();
        MkvManagerScraper::$isCacheEnabled = false;
        $movies = $scraper->searchMovies( md5( "abc" ) );

        self::assertFalse( $movies );
    }

    public function testGetMovieDetails()
    {
        $scraper = new self::$class;
        MkvManagerScraper::$isCacheEnabled = false;
        $movie = $scraper->getMovieDetails( 128357 );

        print_r( $movie );
    }

    /*public function testReleasesList()
    {
        $scraper = new MkvManagerScraperSubsynchro();
        MkvManagerScraper::$isCacheEnabled = false;
        $releases = $scraper->releasesList( "2003/kill-bill--volume-1.html" );

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
        $scraper = new MkvManagerScraperSubsynchro();
        MkvManagerScraper::$isCacheEnabled = false;
        $subtitles = $scraper->getReleaseSubtitles( "2003/kill-bill--volume-1/kill-bill-2003-720p-bluray-x264-septic.html" );

        $contents = array( '2003/kill-bill--volume-1/kill-bill-2003-720p-bluray-x264-septic/fichier-3914.html' );

        self::assertEquals( $contents, $subtitles );
    }

    public function testDownloadSubtitle()
    {
        $scraper = new MkvManagerScraperSubsynchro();
        MkvManagerScraper::$isCacheEnabled = false;
        $path = $scraper->downloadSubtitle( "2001/monstres--cie/monsters-inc-2001-bdrip-h264-5-1ch-secretmyth/fichier-5584.html" );
        self::assertEquals( '2899b6063dfcc8bccc7d5c0d28287867', md5_file( $path ) );
    }*/

    private static $class = 'MkvManagerScraperAllocine';
}
?>
