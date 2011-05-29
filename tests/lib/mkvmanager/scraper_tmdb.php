<?php
ini_set( 'include_path', ".:/usr/share/php");

if ( !defined('PHPUnit_MAIN_METHOD' ) )
{
    define('PHPUnit_MAIN_METHOD', 'MkvManagerScraperTMDBTest::main' );
}

require 'ezc/Base/ezc_bootstrap.php';

require_once 'lib/mkvmanager/interfaces/scraper.php';
require_once 'lib/mkvmanager/scraper_tmdb.php';

require_once 'lib/Info/Movie/SearchResult.php';
require_once 'lib/Info/Movie/Details.php';
require_once 'lib/Info/Person.php';
require_once 'lib/Info/Actor.php';
require_once 'lib/Info/Trailer.php';
require_once 'lib/Info/Director.php';
require_once 'lib/Info/Image.php';

class MkvManagerScraperTMDBTest extends PHPUnit_Framework_TestCase
{
    public function testSearch()
    {
        $scraper = new self::$class();
        MkvManagerScraper::$isCacheEnabled = false;
        $movies = $scraper->searchMovies( "lord of the rings" );

        /*$contents = array (
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

        self::assertEquals( $contents, $movies );*/
    }

    public function testSearchNoMoviesResults()
    {
        $scraper = new self::$class();
        MkvManagerScraper::$isCacheEnabled = false;
        $movies = $scraper->searchMovies( "foobar" );

        self::assertFalse( $movies );
    }

    public function testGetImages()
    {
        $scraper = new self::$class();
        MkvManagerScraper::$isCacheEnabled = false;
        $images = $scraper->getImages( 122 );
        print_r( $images );
    }

    private static $class = 'MkvManagerScraperTMDB';
}
?>
