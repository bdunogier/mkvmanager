<?php
include "tests/config.php";

if ( !defined('PHPUnit_MAIN_METHOD' ) )
{
    define('PHPUnit_MAIN_METHOD', 'mmMvcControllersMovieTest::main' );
}

require_once 'lib/mvc/controllers/movie.php';
require_once 'lib/mkvmanager/interfaces/scraper.php';
require_once 'lib/mkvmanager/scraper_allocine.php';
require_once 'lib/mkvmanager/scraper_tmdb.php';

require_once 'lib/Info/Movie/SearchResult.php';
require_once 'lib/Info/Movie/Details.php';
require_once 'lib/Info/Person.php';
require_once 'lib/Info/Actor.php';
require_once 'lib/Info/Trailer.php';
require_once 'lib/Info/Director.php';
require_once 'lib/Info/Image.php';
require_once 'lib/Xbmc/Nfo/Writers/Movie.php';

class mmMvcControllersMovieTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        MkvManagerScraper::$isCacheEnabled = false;
    }

    public function testDoNfoSearch()
    {
        $request = new ezcMvcRequest();
        $request->variables['folder'] = "The return of the king (2003)";

        $controller = new mm\Mvc\Controllers\Movie( 'NfoSearch', $request );
        $result = $controller->createResult();

        self::assertType( 'ezcMvcResult', $result );
        self::assertArrayHasKey( 'results_allocine', $result->variables );
        self::assertArrayHasKey( 'results_tmdb', $result->variables );
    }

    public function testDoNfoGenerate()
    {
        $request = new ezcMvcRequest();
        $request->variables['folder'] = "The return of the king (2003)";
        $request->variables['AllocineId'] = 39187;
        $request->variables['TMDbId'] = 122;
        $request->uri = '/nfo/movie/generate/' . urlencode( "The return of the king (2003)" ) . '/39187/122';

        $controller = new mm\Mvc\Controllers\Movie( 'NfoGenerate', $request );
        $result = $controller->createResult();

        self::assertType( 'ezcMvcResult', $result );
        self::assertEquals( '/nfo/movie/update-info', $result->variables['updateUrl'] );
        self::assertEquals( '/nfo/movie/save/The+return+of+the+king+%282003%29', $result->variables['saveUrl'] );
        self::assertEquals(
            file_get_contents( str_replace( '.php', '_doGenerateNfo.nfo', __FILE__ ) ),
            $result->variables['nfo']
        );
    }

    public function testDoNfoUpdateInfoSelectTrailer()
    {
        $originalInfo = $this->getMovieInfo( true );
        $oldTrailer = clone $originalInfo->trailers[0];
        $newTrailer = clone $originalInfo->trailers[2];

        $request = new ezcMvcRequest();
        $request->variables['info'] = $this->getMovieInfo( true );
        $request->variables['actionType'] = "SelectTrailer";
        $request->variables['actionValue'] = "2";

        self::assertEquals( $oldTrailer, $originalInfo->trailers[0] );

        $controller = new mm\Mvc\Controllers\Movie( 'NfoUpdateInfo', $request );
        $result = $controller->createResult();

        $updatedInfo = eval( "return " . $result->variables['info'] . ";" );

        self::assertEquals( $newTrailer, $updatedInfo->trailers[0] );
        self::assertArrayHasKey( 'nfo', $result->variables );
        self::assertArrayHasKey( 'info', $result->variables );
        self::assertArrayHasKey( 'status', $result->variables );
        self::assertEquals( 'ok', $result->variables['status'] );
    }

    public function testDoNfoUpdateInfoSelectMainPoster()
    {
        $originalInfo = $this->getMovieInfo( true );
        $oldPoster = clone $originalInfo->posters[0];
        $newPoster = clone $originalInfo->posters[2];

        $request = new ezcMvcRequest();
        $request->variables['info'] = $this->getMovieInfo( true );
        $request->variables['actionType'] = "SelectMainPoster";
        $request->variables['actionValue'] = "2";

        self::assertEquals( $oldPoster, $originalInfo->posters[0] );

        $controller = new mm\Mvc\Controllers\Movie( 'NfoUpdateInfo', $request );
        $result = $controller->createResult();

        $updatedInfo = eval( "return " . $result->variables['info'] . ";" );

        self::assertEquals( $newPoster, $updatedInfo->posters[0] );
    }

    public function testDoNfoUpdateInfoSelectMainFanart()
    {
        $originalInfo = $this->getMovieInfo( true );
        $oldFanart = clone $originalInfo->fanarts[0];
        $newFanart = clone $originalInfo->fanarts[2];

        $request = new ezcMvcRequest();
        $request->variables['info'] = $this->getMovieInfo( true );
        $request->variables['actionType'] = "SelectMainFanart";
        $request->variables['actionValue'] = "2";

        self::assertEquals( $oldFanart, $originalInfo->fanarts[0] );

        $controller = new mm\Mvc\Controllers\Movie( 'NfoUpdateInfo', $request );
        $result = $controller->createResult();

        $updatedInfo = eval( "return " . $result->variables['info'] . ";" );

        self::assertEquals( $newFanart, $updatedInfo->fanarts[0] );
    }

    public function testDoNfoUpdateInfoDisableFanart()
    {
        $originalInfo = $this->getMovieInfo( true );
        $fanart = clone $originalInfo->fanarts[2];

        $request = new ezcMvcRequest();
        $request->variables['info'] = $this->getMovieInfo( true );
        $request->variables['actionType'] = "DisableFanart";
        $request->variables['actionValue'] = "2";

        self::assertEquals( $fanart, $originalInfo->fanarts[2] );

        $controller = new mm\Mvc\Controllers\Movie( 'NfoUpdateInfo', $request );
        $result = $controller->createResult();

        $updatedInfo = eval( "return " . $result->variables['info'] . ";" );

        // @todo Also test the value
        self::assertEquals( count( $originalInfo->fanarts ) - 1, count( $updatedInfo->fanarts ) );
    }

    public function testDoNfoUpdateInfoDisablePoster()
    {
        $originalInfo = $this->getMovieInfo( true );
        $poster = clone $originalInfo->posters[2];

        $request = new ezcMvcRequest();
        $request->variables['info'] = $this->getMovieInfo( true );
        $request->variables['actionType'] = "DisablePoster";
        $request->variables['actionValue'] = "2";

        self::assertEquals( $poster, $originalInfo->posters[2] );

        $controller = new mm\Mvc\Controllers\Movie( 'NfoUpdateInfo', $request );
        $result = $controller->createResult();

        $updatedInfo = eval( "return " . $result->variables['info'] . ";" );

        self::assertEquals( count( $originalInfo->posters ) - 1, count( $updatedInfo->posters ) );
    }

    public function testDoNfoSave()
    {
        $request = new ezcMvcRequest();
        $request->variables['info'] = $this->getMovieInfo( true );
        $request->variables['folder'] = "The return of the king (2003)";

        $controller = new mm\Mvc\Controllers\Movie( 'NfoSave', $request );
        $result = $controller->createResult();

        self::assertEquals( 'tests/runtime/storage/Movies/The return of the king (2003)/The return of the king (2003).nfo', $result->variables['filepath_nfo'] );
        self::assertFileEquals( 'tests/lib/mvc/controllers/movie_doSaveNfo.nfo', $result->variables['filepath_nfo'] );
        @unlink( $result->variables['filepath_nfo'] );
    }

    private function getMovieInfo( $asObject = false )
    {
        $return = "mm\Info\Movie\Details::__set_state(array(
           'plot' => 'La lutte d\'un peloton de Marines contre une invasion d\'aliens dans les rues de Los Angeles...',
           'synopsis' => 'Au camp Pendleton, base militaire située à proximité de Los Angeles, un groupe de Marines, dirigé par le sergent Michael Nantz, est appelé à riposter immédiatement à l\'une des nombreuses attaques qui touchent les littoraux à travers le monde. Le sergent Nantz et ses hommes vont mener une bataille acharnée contre un ennemi mystérieux qui est déterminé à s\'emparer de l\'approvisionnement en eau et à détruire tout sur son passage.',
           'genre' =>
          array (
            0 => 'Action',
            1 => 'Science fiction',
            2 => 'Aventure',
          ),
           'score' => 5.8,
           'trailers' =>
          array (
            0 =>
            mm\Info\Trailer::__set_state(array(
               'title' => 'World Invasion : Battle Los Angeles Bande-annonce VF',
               'url' => 'http://hd.fr.mediaplayer.allocine.fr/nmedia/18/78/91/37/19192953_fa4_vf_hd_001.flv',
               'language' => 'Français',
            )),
            1 =>
            mm\Info\Trailer::__set_state(array(
               'title' => 'World Invasion : Battle Los Angeles Bande-annonce (2) VO',
               'url' => 'http://hd.fr.mediaplayer.allocine.fr/nmedia/18/78/91/37/19190054_fa3_vost_hd_001.flv',
               'language' => 'Anglais',
            )),
            2 =>
            mm\Info\Trailer::__set_state(array(
               'title' => 'World Invasion : Battle Los Angeles Bande-annonce (3) VO',
               'url' => 'http://hd.fr.mediaplayer.allocine.fr/nmedia/18/78/91/37/19181420_fa2_vo_hd_001.flv',
               'language' => 'Anglais',
            )),
            3 =>
            mm\Info\Trailer::__set_state(array(
               'title' => 'World Invasion : Battle Los Angeles Bande-annonce (4) VO',
               'url' => 'http://hd.fr.mediaplayer.allocine.fr/nmedia/18/78/91/37/19174294_fa1_vost_hd_001.flv',
               'language' => 'Anglais',
            )),
          ),
           'posters' =>
          array (
            0 =>
            mm\Info\Image::__set_state(array(
               'type' => 'poster',
               'fullUrl' => 'http://images.allocine.fr/medias/nmedia/18/82/95/58/19660916.jpg',
               'thumbnailUrl' => NULL,
               'width' => NULL,
               'height' => NULL,
            )),
            1 =>
            mm\Info\Image::__set_state(array(
               'type' => 'poster',
               'fullUrl' => 'http://images.allocine.fr/medias/nmedia/18/82/95/58/19653824.jpg',
               'thumbnailUrl' => NULL,
               'width' => NULL,
               'height' => NULL,
            )),
            2 =>
            mm\Info\Image::__set_state(array(
               'type' => 'poster',
               'fullUrl' => 'http://images.allocine.fr/medias/nmedia/18/78/91/37/19487567.jpg',
               'thumbnailUrl' => NULL,
               'width' => NULL,
               'height' => NULL,
            )),
            3 =>
            mm\Info\Image::__set_state(array(
               'type' => 'poster',
               'fullUrl' => 'http://images.allocine.fr/medias/nmedia/18/78/91/37/19487568.jpg',
               'thumbnailUrl' => NULL,
               'width' => NULL,
               'height' => NULL,
            )),
            4 =>
            mm\Info\Image::__set_state(array(
               'type' => 'poster',
               'fullUrl' => 'http://images.allocine.fr/medias/nmedia/18/78/91/37/19487569.jpg',
               'thumbnailUrl' => NULL,
               'width' => NULL,
               'height' => NULL,
            )),
            5 =>
            mm\Info\Image::__set_state(array(
               'type' => 'poster',
               'fullUrl' => 'http://images.allocine.fr/medias/nmedia/18/78/91/37/19487570.jpg',
               'thumbnailUrl' => NULL,
               'width' => NULL,
               'height' => NULL,
            )),
            6 =>
            mm\Info\Image::__set_state(array(
               'type' => 'poster',
               'fullUrl' => 'http://images.allocine.fr/medias/nmedia/18/78/91/37/19487571.jpg',
               'thumbnailUrl' => NULL,
               'width' => NULL,
               'height' => NULL,
            )),
            7 =>
            mm\Info\Image::__set_state(array(
               'type' => 'poster',
               'fullUrl' => 'http://cf1.imgobject.com/posters/0fd/4ddf05a45e73d66b190000fd/battle-los-angeles-original.jpg',
               'thumbnailUrl' => 'http://cf1.imgobject.com/posters/0fd/4ddf05a45e73d66b190000fd/battle-los-angeles-thumb.jpg',
               'width' => 1520,
               'height' => 2146,
            )),
            8 =>
            mm\Info\Image::__set_state(array(
               'type' => 'poster',
               'fullUrl' => 'http://cf1.imgobject.com/posters/9e6/4dd35ad57b9aa134b00019e6/battle-los-angeles-original.jpg',
               'thumbnailUrl' => 'http://cf1.imgobject.com/posters/9e6/4dd35ad57b9aa134b00019e6/battle-los-angeles-thumb.jpg',
               'width' => 1000,
               'height' => 1500,
            )),
            9 =>
            mm\Info\Image::__set_state(array(
               'type' => 'poster',
               'fullUrl' => 'http://cf1.imgobject.com/posters/e1c/4d3e194d5e73d622cf001e1c/battle-los-angeles-original.jpg',
               'thumbnailUrl' => 'http://cf1.imgobject.com/posters/e1c/4d3e194d5e73d622cf001e1c/battle-los-angeles-thumb.jpg',
               'width' => 1012,
               'height' => 1500,
            )),
            10 =>
            mm\Info\Image::__set_state(array(
               'type' => 'poster',
               'fullUrl' => 'http://cf1.imgobject.com/posters/5e3/4d973cce7b9aa119a00045e3/battle-los-angeles-original.jpg',
               'thumbnailUrl' => 'http://cf1.imgobject.com/posters/5e3/4d973cce7b9aa119a00045e3/battle-los-angeles-thumb.jpg',
               'width' => 1000,
               'height' => 1500,
            )),
            11 =>
            mm\Info\Image::__set_state(array(
               'type' => 'poster',
               'fullUrl' => 'http://cf1.imgobject.com/posters/efd/4d5ef68d5e73d60c68001efd/battle-los-angeles-original.jpg',
               'thumbnailUrl' => 'http://cf1.imgobject.com/posters/efd/4d5ef68d5e73d60c68001efd/battle-los-angeles-thumb.jpg',
               'width' => 981,
               'height' => 1450,
            )),
            12 =>
            mm\Info\Image::__set_state(array(
               'type' => 'poster',
               'fullUrl' => 'http://cf1.imgobject.com/posters/6b3/4d0ca5a85e73d637130006b3/battle-los-angeles-original.jpg',
               'thumbnailUrl' => 'http://cf1.imgobject.com/posters/6b3/4d0ca5a85e73d637130006b3/battle-los-angeles-thumb.jpg',
               'width' => 864,
               'height' => 1280,
            )),
            13 =>
            mm\Info\Image::__set_state(array(
               'type' => 'poster',
               'fullUrl' => 'http://cf1.imgobject.com/posters/519/4d6a65267b9aa13629002519/battle-los-angeles-original.jpg',
               'thumbnailUrl' => 'http://cf1.imgobject.com/posters/519/4d6a65267b9aa13629002519/battle-los-angeles-thumb.jpg',
               'width' => 600,
               'height' => 800,
            )),
            14 =>
            mm\Info\Image::__set_state(array(
               'type' => 'poster',
               'fullUrl' => 'http://cf1.imgobject.com/posters/d1c/4d619bfe5e73d63105001d1c/battle-los-angeles-original.jpg',
               'thumbnailUrl' => 'http://cf1.imgobject.com/posters/d1c/4d619bfe5e73d63105001d1c/battle-los-angeles-thumb.jpg',
               'width' => 800,
               'height' => 1200,
            )),
            15 =>
            mm\Info\Image::__set_state(array(
               'type' => 'poster',
               'fullUrl' => 'http://cf1.imgobject.com/posters/3d5/4c89635a5e73d66b5d0003d5/battle-los-angeles-original.jpg',
               'thumbnailUrl' => 'http://cf1.imgobject.com/posters/3d5/4c89635a5e73d66b5d0003d5/battle-los-angeles-thumb.jpg',
               'width' => 666,
               'height' => 942,
            )),
            16 =>
            mm\Info\Image::__set_state(array(
               'type' => 'poster',
               'fullUrl' => 'http://cf1.imgobject.com/posters/45b/4cdd5b0b7b9aa137fe00045b/battle-los-angeles-original.jpg',
               'thumbnailUrl' => 'http://cf1.imgobject.com/posters/45b/4cdd5b0b7b9aa137fe00045b/battle-los-angeles-thumb.jpg',
               'width' => 666,
               'height' => 987,
            )),
            17 =>
            mm\Info\Image::__set_state(array(
               'type' => 'poster',
               'fullUrl' => 'http://cf1.imgobject.com/posters/3ea/4c8963315e73d66b5b0003ea/battle-los-angeles-original.jpg',
               'thumbnailUrl' => 'http://cf1.imgobject.com/posters/3ea/4c8963315e73d66b5b0003ea/battle-los-angeles-thumb.jpg',
               'width' => 666,
               'height' => 942,
            )),
            18 =>
            mm\Info\Image::__set_state(array(
               'type' => 'poster',
               'fullUrl' => 'http://cf1.imgobject.com/posters/337/4db87ebc7b9aa14209000337/battle-los-angeles-original.jpg',
               'thumbnailUrl' => 'http://cf1.imgobject.com/posters/337/4db87ebc7b9aa14209000337/battle-los-angeles-thumb.jpg',
               'width' => 666,
               'height' => 938,
            )),
            19 =>
            mm\Info\Image::__set_state(array(
               'type' => 'poster',
               'fullUrl' => 'http://cf1.imgobject.com/posters/c0c/4de068fd5e73d65a09007c0c/battle-los-angeles-original.jpg',
               'thumbnailUrl' => 'http://cf1.imgobject.com/posters/c0c/4de068fd5e73d65a09007c0c/battle-los-angeles-thumb.jpg',
               'width' => 1000,
               'height' => 1500,
            )),
          ),
           'fanarts' =>
          array (
            0 =>
            mm\Info\Image::__set_state(array(
               'type' => 'fanart',
               'fullUrl' => 'http://cf1.imgobject.com/backdrops/5a8/4d9737ee7b9aa119a00045a8/battle-los-angeles-original.jpg',
               'thumbnailUrl' => 'http://cf1.imgobject.com/backdrops/5a8/4d9737ee7b9aa119a00045a8/battle-los-angeles-thumb.jpg',
               'width' => 1920,
               'height' => 1080,
            )),
            1 =>
            mm\Info\Image::__set_state(array(
               'type' => 'fanart',
               'fullUrl' => 'http://cf1.imgobject.com/backdrops/6d9/4d973d427b9aa119940046d9/battle-los-angeles-original.jpg',
               'thumbnailUrl' => 'http://cf1.imgobject.com/backdrops/6d9/4d973d427b9aa119940046d9/battle-los-angeles-thumb.jpg',
               'width' => 1920,
               'height' => 1080,
            )),
            2 =>
            mm\Info\Image::__set_state(array(
               'type' => 'fanart',
               'fullUrl' => 'http://cf1.imgobject.com/backdrops/7c7/4d9737bb7b9aa12efa0017c7/battle-los-angeles-original.jpg',
               'thumbnailUrl' => 'http://cf1.imgobject.com/backdrops/7c7/4d9737bb7b9aa12efa0017c7/battle-los-angeles-thumb.jpg',
               'width' => 1280,
               'height' => 720,
            )),
            3 =>
            mm\Info\Image::__set_state(array(
               'type' => 'fanart',
               'fullUrl' => 'http://cf1.imgobject.com/backdrops/98a/4d954c575e73d6225d00298a/battle-los-angeles-original.jpg',
               'thumbnailUrl' => 'http://cf1.imgobject.com/backdrops/98a/4d954c575e73d6225d00298a/battle-los-angeles-thumb.jpg',
               'width' => 1920,
               'height' => 1080,
            )),
            4 =>
            mm\Info\Image::__set_state(array(
               'type' => 'fanart',
               'fullUrl' => 'http://cf1.imgobject.com/backdrops/b50/4d954c385e73d623b0001b50/battle-los-angeles-original.jpg',
               'thumbnailUrl' => 'http://cf1.imgobject.com/backdrops/b50/4d954c385e73d623b0001b50/battle-los-angeles-thumb.jpg',
               'width' => 1920,
               'height' => 1080,
            )),
            5 =>
            mm\Info\Image::__set_state(array(
               'type' => 'fanart',
               'fullUrl' => 'http://cf1.imgobject.com/backdrops/6ad/4d9737cf7b9aa1199a0046ad/battle-los-angeles-original.jpg',
               'thumbnailUrl' => 'http://cf1.imgobject.com/backdrops/6ad/4d9737cf7b9aa1199a0046ad/battle-los-angeles-thumb.jpg',
               'width' => 1280,
               'height' => 720,
            )),
            6 =>
            mm\Info\Image::__set_state(array(
               'type' => 'fanart',
               'fullUrl' => 'http://cf1.imgobject.com/backdrops/4cd/4d971c577b9aa1199a0044cd/battle-los-angeles-original.jpg',
               'thumbnailUrl' => 'http://cf1.imgobject.com/backdrops/4cd/4d971c577b9aa1199a0044cd/battle-los-angeles-thumb.jpg',
               'width' => 1280,
               'height' => 720,
            )),
            7 =>
            mm\Info\Image::__set_state(array(
               'type' => 'fanart',
               'fullUrl' => 'http://cf1.imgobject.com/backdrops/5b5/4ddc250a5e73d65a030045b5/battle-los-angeles-original.jpg',
               'thumbnailUrl' => 'http://cf1.imgobject.com/backdrops/5b5/4ddc250a5e73d65a030045b5/battle-los-angeles-thumb.jpg',
               'width' => 1280,
               'height' => 720,
            )),
            8 =>
            mm\Info\Image::__set_state(array(
               'type' => 'fanart',
               'fullUrl' => 'http://cf1.imgobject.com/backdrops/e82/4dd3a9f87b9aa134b3001e82/battle-los-angeles-original.jpg',
               'thumbnailUrl' => 'http://cf1.imgobject.com/backdrops/e82/4dd3a9f87b9aa134b3001e82/battle-los-angeles-thumb.jpg',
               'width' => 2025,
               'height' => 1139,
            )),
            9 =>
            mm\Info\Image::__set_state(array(
               'type' => 'fanart',
               'fullUrl' => 'http://cf1.imgobject.com/backdrops/8ee/4d954c045e73d622740028ee/battle-los-angeles-original.jpg',
               'thumbnailUrl' => 'http://cf1.imgobject.com/backdrops/8ee/4d954c045e73d622740028ee/battle-los-angeles-thumb.jpg',
               'width' => 1920,
               'height' => 1080,
            )),
          ),
           'actors' =>
          array (
            0 =>
            mm\Info\Actor::__set_state(array(
               'role' => 'Sergent Michael Nantz',
               'name' => 'Aaron Eckhart',
               'image' => 'http://images.allocine.fr/medias/nmedia/18/35/25/32/19720631.jpg',
            )),
            1 =>
            mm\Info\Actor::__set_state(array(
               'role' => 'Sergent Chef Elena Santos',
               'name' => 'Michelle Rodriguez',
               'image' => 'http://images.allocine.fr/medias/nmedia/18/35/36/07/19603216.jpg',
            )),
            2 =>
            mm\Info\Actor::__set_state(array(
               'role' => '2nd Lieutenant William Martinez',
               'name' => 'Ramon Rodríguez',
               'image' => 'http://images.allocine.fr/medias/nmedia/18/62/87/65/19117719.jpg',
            )),
            3 =>
            mm\Info\Actor::__set_state(array(
               'role' => 'Michele',
               'name' => 'Bridget Moynahan',
               'image' => 'http://images.allocine.fr/medias/nmedia/18/35/33/21/19121514.jpg',
            )),
            4 =>
            mm\Info\Actor::__set_state(array(
               'role' => 'Caporal Kevin Harris',
               'name' => 'Ne-Yo',
               'image' => 'http://images.allocine.fr/medias/nmedia/18/64/19/65/18758659.jpg',
            )),
            5 =>
            mm\Info\Actor::__set_state(array(
               'role' => 'Joe Rincon',
               'name' => 'Michael Peña',
               'image' => 'http://images.allocine.fr/medias/nmedia/18/63/00/60/19228737.jpg',
            )),
            6 =>
            mm\Info\Actor::__set_state(array(
               'role' => 'Lieutenant caporal Peter Kerns',
               'name' => 'Jim Parrack',
               'image' => 'http://images.allocine.fr/medias/nmedia/18/67/43/99/19535477.jpg',
            )),
            7 =>
            mm\Info\Actor::__set_state(array(
               'role' => 'Caporal Scott Grayston',
               'name' => 'Lucas Till',
               'image' => 'http://images.allocine.fr/medias/nmedia/18/69/56/28/19067507.jpg',
            )),
            8 =>
            mm\Info\Actor::__set_state(array(
               'role' => 'Premier soldat de première classe Shaun Lenihan',
               'name' => 'Noel Fisher',
               'image' => 'http://images.allocine.fr/medias/nmedia/18/36/30/26/19679914.jpg',
            )),
            9 =>
            mm\Info\Actor::__set_state(array(
               'role' => 'Lieutenant Caporal Corey Simmons',
               'name' => 'Taylor Handley',
               'image' => 'http://images.allocine.fr/medias/nmedia/18/63/85/60/18721076.jpg',
            )),
            10 =>
            mm\Info\Actor::__set_state(array(
               'role' => 'Lieutenant caporal Steven Mottola',
               'name' => 'James Hiroyuki Liao',
               'image' => 'http://images.allocine.fr/medias/nmedia/18/67/20/14/18973353.jpg',
            )),
            11 =>
            mm\Info\Actor::__set_state(array(
               'role' => 'Kirsten',
               'name' => 'Joey King',
               'image' => 'http://images.allocine.fr/medias/nmedia/18/82/66/34/19630758.jpg',
            )),
          ),
           'directors' =>
          array (
            0 =>
            mm\Info\Director::__set_state(array(
               'name' => 'Jonathan Liebesman',
               'image' => 'http://images.allocine.fr/medias/nmedia/18/63/85/41/18720785.jpg',
            )),
          ),
           'runtime' => NULL,
           'originalTitle' => 'Battle: Los Angeles',
           'title' => 'World Invasion : Battle Los Angeles',
           'link' => NULL,
           'thumbnail' => NULL,
           'id' => 145364,
           'productionYear' => '2011',
           'releaseDate' => '2011-03-16',
           'directorsShort' => NULL,
           'actorsShort' => NULL,
           'url' => NULL,
        ))";

        return ( $asObject ? eval( "return $return;" ) : $return );
    }
}
?>
