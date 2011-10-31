<?php
/**
 * Scraper for allocine.fr using the json API
 * 1. search
 *
 * 2. details
 * http://api.allocine.fr/xml/movie?format=h264&version=2&json=0&partner=1&profile=large&code=129477
 *
 * 3. trailer
 * get media id from details:
 *   <media jsonListItem="1" class="video" code="18356598">
 * http://www.allocine.fr/skin/video/AcVisionData_xml.asp?media=18356598
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
        // allocine won't accept a year in the query string
        if ( preg_match( '/^(.*) \(([0-9]{4})\)$/', $queryString, $m ) )
        {
            $queryString = $m[1];
            $year = $m[2];
        }
        $queryString = preg_replace( "/(.')/", '', $queryString );

        $url = sprintf( "$this->baseURL/$this->searchURI", urlencode( $queryString ) );

        $doc = $this->fetch( $url, 'parseFromXMLToXML' );
        $results = array();
        foreach( $doc->movie as $movie )
        {
            $result = new mm\Info\Movie\SearchResult();

            $result->id = (int)$movie['code'];
            $result->originalTitle = (string)$movie->originalTitle;
            $result->title = (string)$movie->title;
            $result->productionYear = (int)$movie->productionYear;
            $result->releaseDate = (string)$movie->release->releaseDate;
            $result->releaseYear = $movie->release->releaseDate == "" ? (int)$movie->productionYear : substr( $movie->release->releaseDate, 0, 4 );
            $result->directorsShort = explode(', ', (string)$movie->castingShort->directors );
            $result->actorsShort = explode( ', ', (string)$movie->castingShort->actors );

            $result->thumbnail = (string)$movie->poster['href'];

            $result->url = (string)$movie->linkList->link['href'];


            $results[] = $result;
        }

        if ( !count( $results ) )
            $results = false;

        return $results;
    }

    /**
     * Fetches the details for the movie with id $movieId
     * @param int $movieId
     * @return MkvManagerScraperAllocineResult
     */
    public function getMovieDetails( $movieId )
    {
        $url = sprintf( "$this->baseURL/$this->movieURI", $movieId );

        $doc = $this->fetch( $url, 'parseFromXMLToXML'  );
        try {
            $result = new mm\Info\Movie\Details();
        } catch ( Exception $e ) {
            error_log( print_r( $e, true ) );
        }

        $result->id = (int)$doc['code'];
        $result->originalTitle = (string)$doc->originalTitle;
        $result->title = (string)$doc->title;
        $result->plot = (string)$doc->synopsisShort;
        $result->synopsis = (string)$doc->synopsis;
        $result->releaseDate = (string)$doc->release->releaseDate;
        $result->productionYear = (string)$doc->productionYear;
        $result->score = (float)$doc->statistics->pressRating + (float)$doc->statistics->userRating;

        // actors
        if( isset( $doc->casting->castMember ) )
        {
        foreach( $doc->casting->castMember as $person )
        {
            if ( (string)$person->picture['href'] == '' )
                continue;

            // actor
            if ( (int)$person->activity['code'] == 8001 )
            {
                if ( (string)$person->role == '' )
                    continue;
                $personResult = new mm\Info\Actor();
                $personResult->name = (string)$person->person->name;
                $personResult->image = (string)$person->picture[0]['href'];
                $personResult->role = (string)$person->role;
                $result->actors[] = $personResult;
                continue;
            }

            // director
            if ( (int)$person->activity['code'] == 8002 )
            {
                $personResult = new mm\Info\Director();
                $personResult->name = (string)$person->person->name;
                $personResult->image = (string)$person->picture[0]['href'];
                $result->directors[] = $personResult;
                continue;
            }
        }
        }

        if( isset( $doc->genreList->genre ) )
        {
            foreach( $doc->genreList->genre as $genre )
            {
                $result->genre[] = (string)$genre;
            }
        }

        if( isset( $doc->mediaList->media ) )
        {
            foreach( $doc->mediaList->media as $media )
	    {
                if ( (string)$media['class'] == 'picture' )
                {
                    // affiches seulement
                    if ( (int)$media->type['code'] != 31001 and (int)$media->type['code'] != 31125 )
                        continue;

                    $result->posters[] = new \mm\Info\Image( 'poster', (string)$media->thumbnail['href'] );
                }
                elseif ( (string)$media['class'] == 'video' )
                {
                    if ( (int)$media->type['code'] != 31003 )
                        continue;
                    $trailerCode = (int)$media['code'];

                    $uri = $uri = sprintf( $this->trailerURI, $trailerCode );
                    $videoNode = $this->fetch( $uri, 'parseFromXMLToXMLWithUTF8Conversion' )->AcVisionVideo;
                    $trailerHref = false;
                    if ( (string)$videoNode['hd_path'] != '' )
                        $trailerHref = (string)$videoNode['hd_path'];
                    elseif ( (string)$videoNode['md_path'] != '' )
                        $trailerHref = (string)$videoNode['md_path'];
                    elseif ( (string)$videoNode['ld_path'] != '' )
                        $trailerHref = (string)$videoNode['ld_path'];

                    if ( $trailerHref !== false )
                    {
                        $trailerObject = new mm\Info\Trailer();
                        $trailerObject->title = (string)$media->title;
                        $trailerObject->url = $trailerHref;
                        $trailerObject->language = (string)$media->version;
                        $result->trailers[] = $trailerObject;
                    }
                }
            }
        }

        return $result;
    }

    public function get()
    {

    }

    protected $baseURL = 'http://api.allocine.fr';
    protected $movieURI = 'xml/movie?code=%s&format=xml&media=mp4-lc&partner=YW5kcm9pZC12Mg&profile=large&version=2';
    protected $searchURI = 'xml/search?partner=3&q=%s';
    protected $trailerURI = 'http://www.allocine.fr/skin/video/AcVisionData_xml.asp?media=%d';
}
?>
