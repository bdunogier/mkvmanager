<?php
ini_set( 'include_path', ".:/usr/share/php");

if ( !defined('PHPUnit_MAIN_METHOD' ) )
{
    define('PHPUnit_MAIN_METHOD', 'mmXbmcNfoWritersMovieTest::main' );
}

require 'ezc/Base/ezc_bootstrap.php';

require_once 'lib/Xbmc/Nfo/Writers/Movie.php';
require_once 'lib/mkvmanager/interfaces/scraper.php';
require_once 'lib/mkvmanager/scraper_allocine.php';
require_once 'lib/mkvmanager/exceptions/scraper_html.php';
require_once 'lib/mkvmanager/exceptions/scraper_http.php';

require_once 'lib/Info/Movie/SearchResult.php';
require_once 'lib/Info/Movie/Details.php';
require_once 'lib/Info/Person.php';
require_once 'lib/Info/Actor.php';
require_once 'lib/Info/Trailer.php';
require_once 'lib/Info/Director.php';

class mmXbmcNfoWritersMovieTest extends PHPUnit_Framework_TestCase
{
    private function getInfoObject()
    {
        return mm\Info\Movie\Details::__set_state(array(
           'plot' => 'Les aventures de Largo Winch, un jeune héritier milliardaire fraîchement plongé dans les méandres de la haute finance.',
           'synopsis' => 'Le milliardaire Nerio Winch est retrouvé noyé. Une mort forcément suspecte quand on sait qu\'il s\'agit du fondateur et principal actionnaire du puissant et tentaculaire Groupe W.Qui va hériter de cet empire économique ? Officiellement Nerio n\'avait pas de famille. Mais il cachait un secret : un fils, Largo, adopté presque trente ans plus tôt dans un orphelinat bosniaque. Seul problème, ce jeune héritier vient d\'être jeté dans une prison du fin fond de l\'Amazonie. Accusé de trafic de drogue, il clame son innocence.Nerio assassiné. Largo emprisonné. Et si ces deux affaires faisaient partie d\'un seul et même complot visant à prendre le contrôle de l\'empire Winch ?',
           'genre' =>
          array (
            0 => 'Action',
            1 => 'Aventure',
          ),
           'score' => 6.4,
           'trailers' =>
          array (
            0 =>
            mm\Info\Trailer::__set_state(array(
               'title' => 'Largo Winch Bande-annonce VF',
               'url' => 'http://hd.fr.mediaplayer.allocine.fr/nmedia/18/64/71/36/18843610_fa2_vf_hd.flv',
               'language' => 'Français',
            )),
            1 =>
            mm\Info\Trailer::__set_state(array(
               'title' => 'Largo Winch Bande-annonce (2) VO',
               'url' => 'http://hd.fr.mediaplayer.allocine.fr/nmedia/18/64/71/36/18841169_fa1_vost_hd.flv',
               'language' => 'Anglais',
            )),
          ),
           'posters' =>
          array (
            0 => 'http://images.allocine.fr/medias/nmedia/18/64/71/36/19020895.jpg',
          ),
           'fanarts' =>
          array (
          ),
           'actors' =>
          array (
            0 =>
            mm\Info\Actor::__set_state(array(
               'role' => 'Largo Winch',
               'name' => 'Tomer Sisley',
               'image' => 'http://images.allocine.fr/medias/nmedia/18/35/94/86/19667769.jpg',
            )),
            1 =>
            mm\Info\Actor::__set_state(array(
               'role' => 'Ann Ferguson',
               'name' => 'Kristin Scott Thomas',
               'image' => 'http://images.allocine.fr/medias/nmedia/18/35/35/02/19481413.jpg',
            )),
            2 =>
            mm\Info\Actor::__set_state(array(
               'role' => 'Nerio Winch',
               'name' => 'Miki Manojlovic',
               'image' => 'http://images.allocine.fr/medias/nmedia/18/64/19/63/19456916.jpg',
            )),
            3 =>
            mm\Info\Actor::__set_state(array(
               'role' => 'Freddy',
               'name' => 'Gilbert Melki',
               'image' => 'http://images.allocine.fr/medias/nmedia/18/35/39/54/19638204.jpg',
            )),
            4 =>
            mm\Info\Actor::__set_state(array(
               'role' => 'Lea / Naomi',
               'name' => 'Mélanie Thierry',
               'image' => 'http://images.allocine.fr/medias/nmedia/18/36/02/85/19587196.jpg',
            )),
            5 =>
            mm\Info\Actor::__set_state(array(
               'role' => 'Hannah',
               'name' => 'Anne Consigny',
               'image' => 'http://images.allocine.fr/medias/nmedia/18/36/02/92/19122907.jpg',
            )),
            6 =>
            mm\Info\Actor::__set_state(array(
               'role' => 'Mikhail Korsky',
               'name' => 'Karel Roden',
               'image' => 'http://images.allocine.fr/medias/nmedia/18/35/37/71/18758635.jpg',
            )),
            7 =>
            mm\Info\Actor::__set_state(array(
               'role' => 'Goran',
               'name' => 'Rasha Bukvic',
               'image' => 'http://images.allocine.fr/medias/nmedia/18/68/22/51/19015286.jpg',
            )),
            8 =>
            mm\Info\Actor::__set_state(array(
               'role' => 'Largo Winch adolescent',
               'name' => 'Benjamin Siksou',
               'image' => 'http://images.allocine.fr/medias/nmedia/18/79/94/12/19539760.jpg',
            )),
            9 =>
            mm\Info\Actor::__set_state(array(
               'role' => 'Gauthier',
               'name' => 'Nicolas Vaude',
               'image' => 'http://images.allocine.fr/medias/nmedia/18/63/19/99/19667776.jpg',
            )),
            10 =>
            mm\Info\Actor::__set_state(array(
               'role' => 'Melina',
               'name' => 'Bojana Panic',
               'image' => 'http://images.allocine.fr/medias/nmedia/18/63/79/28/18711023.jpg',
            )),
            11 =>
            mm\Info\Actor::__set_state(array(
               'role' => 'Jacques Wallenberg',
               'name' => 'André Oumansky',
               'image' => 'http://images.allocine.fr/medias/nmedia/18/62/83/39/18654285.jpg',
            )),
            12 =>
            mm\Info\Actor::__set_state(array(
               'role' => 'Paramilitary',
               'name' => 'Vincent Haquin',
               'image' => 'http://images.allocine.fr/medias/nmedia/18/83/78/92/19708011.jpg',
            )),
            13 =>
            mm\Info\Actor::__set_state(array(
               'role' => 'Goran',
               'name' => 'Radivoje Bukvic',
               'image' => 'http://images.allocine.fr/medias/nmedia/18/63/14/26/18675660.jpg',
            )),
          ),
           'directors' =>
          array (
            0 =>
            mm\Info\Director::__set_state(array(
               'name' => 'Jérôme Salle',
               'image' => 'http://images.allocine.fr/medias/nmedia/18/68/21/74/19015150.jpg',
            )),
          ),
           'runtime' => NULL,
           'originalTitle' => 'Largo Winch',
           'title' => 'Largo Winch',
           'link' => NULL,
           'thumbnail' => NULL,
           'id' => 128357,
           'productionYear' => '2008',
           'releaseDate' => '2008-12-17',
           'directorsShort' => NULL,
           'actorsShort' => NULL,
        ));
    }

    public function testGet()
    {
        $info = $this->getInfoObject();
        $nfoWriter = new mm\Xbmc\Nfo\Writers\Movie( $info );
        $nfoContents = $nfoWriter->get();
        self::assertEquals( file_get_contents( $this->referenceFile ), $nfoContents );
    }

    public function testWrite()
    {
        $file = 'tmp/tests/' . __METHOD__ . '.nfo';

        $info = $this->getInfoObject();
        $nfoWriter = new mm\Xbmc\Nfo\Writers\Movie( $info );
        $nfoWriter->write( $file );

        self::assertFileEquals( $this->referenceFile, $file );
    }

    private $referenceFile = 'tests/lib/Xbmc/Nfo/Writers/Movie_test_file.nfo';
}
?>
