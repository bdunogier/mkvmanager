<?php
/**
 * File containing the mm\Mvc\Controllers\Movie
 *
 * @version $Id$
 * @copyright 2011
 */

namespace mm\Mvc\Controllers;
use \mm\Xbmc\Nfo\Writers\Movie as NfoWriter;
use \ezcConfigurationManager as ezcConfigurationManager;

class Movie extends \ezcMvcController
{
    /**
     * Searches for the movie $this->query on movie scrapers
     *
     * @param string $this->folder
     *
     * @return ezcMvcResult
     */
    public function doNfoSearch()
    {
        $query = $this->folder;
        $result = new \ezcMvcResult();
        $result->variables['page_title'] = "{$query} :: Search for Movie NFO :: MKV Manager";
        $result->variables['generateUrl'] = "/nfo/movie/generate/" . urlencode( $query );
        foreach( array( 'allocine' => '\MkvManagerScraperAllocine', 'tmdb' => '\MkvManagerScraperTMDB' ) as $identifier => $scraperClass )
        {
            $scraper = new $scraperClass();
            $result->variables["results_{$identifier}"] = $scraper->searchMovies( $query );
        }

        return $result;
    }

    /**
     * Generates the NFO for the movie located in $this->folder ids $this->AllocineId and $this->TBDbId
     *
     * @param $this->folder The movie folder in the movies storage directory
     * @param $this->AllocineId
     * @param $this->TBDbId
     *
     * @return ezcMvcResult
     */
    public function doNfoGenerate()
    {
        $result = new \ezcMvcResult();
        $result->variables['page_title'] = "Generate NFO :: MKV Manager";
        $result->variables['saveUrl'] = "/nfo/movie/save/" . urlencode( $this->folder );

        $allocineId = $this->AllocineId;
        $TMDbId = $this->TMDbId;

        $allocineScraper = new \MkvManagerScraperAllocine();
        // $allocineScraper->isCacheEnabled = false;
        $infos = $allocineScraper->getMovieDetails( $allocineId );

        $TMDbScraper = new \MkvManagerScraperTMDB();
        // $TMDbScraper->isCacheEnabled = false;
        $TMDbImages = $TMDbScraper->getImages( $TMDbId );

        foreach( $TMDbImages as $image )
        {
            if ( $image->type == 'poster' )
            {
                $variable = 'posters';
            }
            elseif ( $image->type == 'fanart' )
            {
                $variable = 'fanarts';
            }
            else
            {
                continue;
            }
            array_push( $infos->$variable, $image );
        }
        $result->variables['infos'] = $infos;

        // nfo
        $writer = new \mm\Xbmc\Nfo\Writers\Movie( $infos );
        $result->variables['nfo'] = $writer->get();

        return $result;
    }

    /**
     * Update the provided information $info using action $action with value $value
     *
     * The result is viewed using AJAX
     *
     * @param mm\Info\Movie\Details $info
     * @param string $actionType
     * @param string $actionValue
     *
     * @return ezcMvcResults
     */
    public function doNfoUpdateInfo()
    {
        $result = new \ezcMvcResult();

        $actionValue = (int)$this->actionValue;
        $info = $this->info;

        switch( $this->actionType )
        {
            // select trailer
            case 'SelectTrailer':
                $info->swap( 'trailers', 0, $actionValue );
                break;

            // select main poster
            case 'SelectMainPoster':
                $info->swap( 'posters', 0, $actionValue );
                break;

                // select main poster
            case 'DisablePoster':
                $info->remove( 'posters', $actionValue );
                break;

                // select main poster
            case 'SelectMainFanart':
                $info->swap( 'fanarts', 0, $actionValue );
                break;

                // select main poster
            case 'DisableFanart':
                $info->remove( 'fanarts', $actionValue );
                break;

            default:
                break;
        }
        $result->variables['status'] = 'ok';
        $result->variables['info'] = var_export( $info, true );

        $nfoWriter = new NfoWriter( $info );
        $result->variables['NFO'] = $nfoWriter->get();
        return $result;
    }

    /**
     * Saves a NFO
     *
     * @return ezcMvcResult
     */
    public function doNfoSave()
    {
        $result = new \ezcMvcResult();

        $info = eval( "return {$this->info};");
        $nfoWriter = new NfoWriter( $info );

        $result->variables['NFO'] = $nfoWriter->write( $filename );

        return $result;
    }
}
?>