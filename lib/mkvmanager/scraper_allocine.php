<?php
/**
 * Scraper allocine
 * 1. search by string
 *    http://www.allocine.fr/recherche/?q=lord+of+the+rings )
 *    => HTML results list
 *    <div class="colcontent">
 *      <div class="rubric">
 *        <div class="vmargin10t">
 *        ...
 *        <div class="vmargin10b">
 *        4 résultats trouvés dans les titres de films.
 *        </div>
 *        <table class="totalwidth noborder purehtml">
 *          <tbody>
 *            <tr>
 *              <td>
 *                <a href="/film/fichefilm_gen_cfilm=38512.html">
 *                  <img src="http://images.allocine.fr/r_75_106/medias/nmedia/18/65/65/36/18880027.jpg" alt="The Lord of the Rings">
 *                </a>
 *              </td>
 *              <td>
 *                <div><div style="margin-top:-5px;">
 *                  <a href="/film/fichefilm_gen_cfilm=38512.html">
 *                    Le Seigneur des anneaux
 *                  </a>
 *                  (The Lord of the Rings)
 *                  <br>
 *                  <span class="fs11">
 *                  1978<br>
 *                  de Ralph Bakshi<br>
 *                  avec Christopher Guard, Michel Caccia<br>
 *                  </span>
 *                </div></div>
 *              </td>
 *            </tr>
 *          </tbody>
 *        </table>
 *    <results>
 *      <rs id="2003/kill-bill--volume-1.html" info="Kill Bill : Volume 1 - 3 releases - 3 fichiers">Kill Bill : Volume 1 (2003)</rs>
 *      <rs id="2003/kill-bill--volume-2.html" info="Kill Bill : Volume 2 - 2 releases - 2 fichiers">Kill Bill : Volume 2 (2003)</rs>
 *    </results>
 *    </code>
 * - search movie details
 *   http://www.allocine.fr/film/fichefilm_gen_cfilm=39187.html
 */
class MkvManagerScraperAllocine extends MkvManagerScraper
{
    /**
     * Returns the list of movies for the search string $query
     * @param string $query
     * @return array(MkvManagerScraperAllocineSearchResult) or false if no results were found
     */
    public function searchMovies( $queryString )
    {
        $this->params['q'] = $queryString;

        $doc = $this->fetch( $this->searchURL );
        $results = array();

        list( $contentDiv ) = $doc->xpath( '//div[@class="colcontent"]' );
        $contentDiv = simplexml_load_string( $contentDiv->asXML() );

        $resultPhraseArray = $contentDiv->xpath( '//div[@class="vmargin10b "]' );
        if ( !count( $resultPhraseArray ) )
        {
            return false;
        }

        list( $resultTable ) = $contentDiv->xpath( '//table' );
        $resultTable = simplexml_load_string( $resultTable->asXML() );
        foreach( $resultTable->xpath( '//tr[count(td)=2]') as $rowNode )
        {
            $result = new MkvManagerScraperAllocineSearchResult();

            $rowNode = simplexml_load_string( $rowNode->asXML() );

            list( $img ) = $rowNode->xpath( '//td//img' );
            $result->thumbnail = (string)$img['src'];

            list( $div ) = $rowNode->xpath( '//td//div/div' );
            $result->originalTitle = trim( (string)$div, "()\t\n\r\0\x0B" );

            list( $a ) = $rowNode->xpath( '//td//div//a' );
            $result->title = trim( (string)$a );
            $result->link = trim( (string)$a['href'] );
            $result->allocineId = (int)substr( $result->link,
                strrpos( $result->link, '=' ) + 1,
                -5
            );

            list( $extraNode ) = $rowNode->xpath( '//span[@class="fs11"]' );
            list( $year, $director, $with ) = explode( "\n", trim( (string)$extraNode ) );
            $result->year = (int)$year;
            $result->director = substr( $director, 3 );
            $result->actors = explode( ', ', substr( $with, 5 ) );

            $results[] = $result;
        }

        return $results;
    }

    /**
     * Fetches the details for the movie with id $movieId
     * @param int $movieId
     * @return MkvManagerScraperAllocineResult
     */
    public function getMovieDetails( $movieId )
    {
        $url = sprintf( $this->detailsURL, $movieId );
        $doc = $this->fetch( $url );

        // print_r( $doc );
        list( $movieNode ) = $doc->xpath( '//div[@typeof="v:Movie"]' );
        $xml = str_replace( array( 'src=" http:=""', 'class="-ico -icofavadd\' ' ), '', $movieNode->asXML() );
        $movieNode = simplexml_load_string( $xml );

        $result = new MkvManagerScraperAllocineResult();
        $result->allocineId = $movieId;
        $result->link = $url;

        // title
        list( $titleNode ) = $movieNode->xpath( '//h1[@property="v:name"]' );
        $result->title = trim( (string)$titleNode );

        // poster thumbnail
        list( $thumbnailNode ) = $movieNode->xpath( '//div[@class="poster"]//a/img' );
        $result->thumbnail = trim( (string)$thumbnailNode['src'] );

        // summary
        list( $summaryNode ) = $movieNode->xpath( '//span[@property="v:summary"]' );
        $result->summary = trim( (string)$summaryNode );

        // directory
        list( $directorNode ) = $movieNode->xpath( '//a[@rel="v:directedBy"]' );
        $result->director = trim( (string)$directorNode );

        // actors
        foreach( $movieNode->xpath( '//a[@rel="v:starring"]' ) as $actor )
        {
            $result->actors[] = trim( (string)$actor );
        }

        // original title
        list( $originalTitle ) = $movieNode->xpath( '//span[@class="purehtml"]/em' );
        $result->originalTitle = trim( (string)$originalTitle );

        // year
        list( $year ) = $movieNode->xpath( '//a[@class="underline" and contains(@href, "?year=")]' );
        $result->year = trim( (string)$year );

        // genres
        foreach( $movieNode->xpath( '//a[@class="underline" and contains(@href, "/film/tous/genre-")]') as $genre )
        {
            $result->genre[] = trim( (string)$genre );
        }

        // votes
        list( $votesNode ) = $movieNode->xpath( '//p[@class="withstars"]/a[contains(@href, "/critiquepublic_")]/img' );
        $result->score = (float)str_replace(',', '.', $votesNode['title'] ) * 2;

        // trailers
        $trailerButtonNodeArray = $movieNode->xpath( '//div[@class="btn_trailer"]/a' );
        if ( count( $trailerButtonNodeArray ) )
        {
            // link to the trailers page, which will give us the full trailers & videos list
            (string)$trailerPageLink = (string)$trailerButtonNodeArray[0]['href'];
            $result->trailerPageLink = $trailerPageLink;

            $trailerUrl = "{$this->siteURL}{$trailerPageLink}";
            $trailerDoc = $this->fetch( $trailerUrl );
            foreach( $trailerDoc->xpath( '//div[@id="carouselcontainer_BA"]//div[@class="datablock"]//div[@class="contenzone"]//a') as $trailerPageAnchor )
            {
                $trailerPageLink = (string)$trailerPageAnchor['href'];
                $trailerTitle = trim( (string)$trailerPageAnchor, "-\t\n\r\0\x0B" );
                if ( substr( $trailerTitle, 0, 3 ) == " - " )
                    $trailerTitle = substr( $trailerTitle, 3 );
                $trailerUrl = "{$this->siteURL}{$trailerPageLink}";
                $trailerDoc = $this->fetch( $trailerUrl );
                $embeds = $trailerDoc->xpath( '//embed' );
                if ( !count( $embeds ) )
                    continue;
                $trailerUrl = (string)$embed['href'];

                $realTrailerUrl = str_replace(
                    array( 'http://h.', '_b_004.mp4' ),
                    array( 'http://hd.', '_hd.flv' ),
                    $trailerUrl
                );
                if ( !file_exists( $realTrailerUrl ) )
                    $realTrailerUrl = $trailerUrl;

                $result->trailers[] = array( 'title' => $trailerTitle, 'href' => $realTrailerUrl );
            }
        }

        // posters
        $postersDoc = $this->fetch( sprintf( $this->postersURL, $movieId ) );
        foreach( $postersDoc->xpath( '//a[contains(@href, "/film/fichefilm-' . $movieId . '/affiches/detail/")]') as $posterAnchor )
        {
            $poster = array( 'thumbnail' => (string)$posterAnchor->img[0]['src'] );
            $posterDetailsUrl = (string)$posterAnchor['href'];

            $posterDetailsDoc = $this->fetch( "{$this->siteURL}{$posterDetailsUrl}" );
            list( $fullImage ) = $posterDetailsDoc->xpath( '//a[@target="_blank" and contains(text(), "Agrandir")]' );
            $poster['href'] = (string)$fullImage['href'];

            $result->posters[] = $poster;

        }
        return $result;
    }

    public function get(){

    }

    protected $baseURL = 'http://www.allocine.fr';
    protected $siteURL = 'http://www.allocine.fr';
    protected $searchURL = 'http://www.allocine.fr/recherche/';
    protected $detailsURL = 'http://www.allocine.fr/film/fichefilm_gen_cfilm=%d.html';
    protected $postersURL = 'http://www.allocine.fr/film/fichefilm-%d/affiches/';
}

class MkvManagerScraperAllocineSearchResult
{
    /**
     * Absolute URL to the movie thumbnail
     * @var string
     */
    public $thumbnail;

    /**
     * Movie title in the original language
     * @var string
     */
    public $originalTitle;

    /**
     * Movie title in french
     * @var string
     */
    public $title;

    /**
     * Allocine relative link to the movie details page
     * @var string
     */
    public $link;

    /**
     * Allocine movie page ID
     * @var int
     */
    public $allocineId;

    /**
     * Movie release year
     * @var int
     */
    public $productionYear;

    /**
     * @var string
     */
    public $directors;

    /**
     * Movie actors
     * @var array( (string)name )
     */
    public $actors;

    public static function __set_state( $array )
    {
        $object = new self;
        foreach( $array as $property => $value )
        {
            if ( property_exists( $object, $property ))
                $object->$property = $value;
        }
        return $object;
    }
}

class MkvManagerScraperAllocineResult extends MkvManagerScraperAllocineSearchResult
{
    public $plot;

    public $synopsis;

    /**
     * Genre
     * @var array(string)
     */
    public $genre;

    public $score;

    public $releaseDate;

    public $trailers = array();

    public $posters = array();
}

class MkvManagerScraperAllocinePerson
{
    public $name;
    public $thumbnail;
}

class MkvManagerScraperAllocineActor extends MkvManagerScraperAllocinePerson
{
    public $role;
}

class MkvManagerScraperAllocineTrailer
{
    public $title;
    public $href;
    public $language;
}
?>
