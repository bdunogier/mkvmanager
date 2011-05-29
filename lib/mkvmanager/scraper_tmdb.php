<?php
/**
 * Scraper for themoviedb.org
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
class MkvManagerScraperTMDB extends MkvManagerScraper
{
    /**
     * Returns the list of movies for the search string $query
     * @param string $query
     * @return array(MkvManagerScraperAllocineSearchResult) or false if no results were found
     */
    public function searchMovies( $queryString )
    {
        $uriReplaceMap = array(
            '%apikey%' => $this->apiKey,
            '%lang%' => 'en',
            '%query%' => urlencode( $queryString ),
        );
        $url = str_replace( array_keys( $uriReplaceMap ), array_values( $uriReplaceMap ), "{$this->baseURL}/{$this->movieSearchURI}" );

        $doc = $this->fetch( $url, 'parseFromXMLToXML' );
        $results = array();
        foreach( $doc->movies->movie as $movie )
        {
            $result = new mm\Info\Movie\SearchResult();

            $result->id = (int)$movie->id;
            $result->originalTitle = (string)$movie->original_name;
            $result->title = (string)$movie->name;
            $result->productionYear = (int)$movie->released;
            $result->releaseDate = (string)$movie->released;
            $result->url = (string)$movie->url;

            foreach( $movie->images->image as $image )
            {
                if ( $image['type'] == "poster" && $image['size'] == 'cover' )
                {
                    $result->thumbnail = (string)$image['url'];
                }
            }

            $results[] = $result;
        }

        if ( !count( $results ) )
            $results = false;

        return $results;
    }

    /**
     * Fetches the details for the movie with id $movieId
     * @param int $movieId
     * @return array(mm\Info\Image)
     */
    public function getImages( $movieId )
    {
        $uriReplaceMap = array(
            '%apikey%' => $this->apiKey,
            '%lang%' => 'en',
            '%tmdbid%' => $movieId,
        );
        $url = str_replace( array_keys( $uriReplaceMap ), array_values( $uriReplaceMap ), "{$this->baseURL}/{$this->movieGetImagesURI}" );

        $doc = $this->fetch( $url, 'parseFromXMLToXML'  );
        try {
            $result = new mm\Info\Movie\Details();
        } catch ( Exception $e ) {
            error_log( print_r( $e, true ) );
        }

        $result = array();
        foreach( $doc->movies->movie->images->backdrop as $image )
        {
            $imageObject = new mm\Info\Image;
            $imageObject->type = 'fanart';

            foreach( $image->image as $imageFile )
            {
                if ( (string)$imageFile['size'] == 'original' )
                {
                    $imageObject->fullUrl = (string)$imageFile['url'];
                    $imageObject->width = (int)$imageFile['width'];
                    $imageObject->height = (int)$imageFile['height'];
                }
                elseif ( (string)$imageFile['size'] == 'thumb' )
                {
                    $imageObject->thumbnailUrl = (string)$imageFile['url'];
                }
            }
            $result[] = $imageObject;
        }

        foreach( $doc->movies->movie->images->poster as $image )
        {
            $imageObject = new mm\Info\Image;
            $imageObject->type = 'poster';

            foreach( $image->image as $imageFile )
            {
                if ( (string)$imageFile['size'] == 'original' )
                {
                    $imageObject->fullUrl = (string)$imageFile['url'];
                    $imageObject->width = (int)$imageFile['width'];
                    $imageObject->height = (int)$imageFile['height'];
                }
                elseif ( (string)$imageFile['size'] == 'thumb' )
                {
                    $imageObject->thumbnailUrl = (string)$imageFile['url'];
                }
            }
            $result[] = $imageObject;
        }

        return $result;
    }

    public function get()
    {

    }

    protected $baseURL = 'http://api.themoviedb.org/2.1';
    protected $movieSearchURI = 'Movie.search/%lang%/xml/%apikey%/%query%';
    protected $movieGetInfoURI = 'Movie.getInfo/%lang%/xml/%apikey%/%tmdbid%';
    protected $movieGetImagesURI = 'Movie.getImages/%lang%/xml/%apikey%/%tmdbid%';

    protected $apiKey = '19b94ef7745f3e09d4a60b01938ddbcd';
}
?>