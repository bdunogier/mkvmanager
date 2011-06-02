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
        foreach( array( 'allocine' => '\MkvManagerScraperAllocine', 'tmdb' => '\MkvManagerScraperTMDB' ) as $identifier => $scraperClass )
        {
            $scraper = new $scraperClass();
            $scrapResults = $scraper->searchMovies( $query );

            foreach( $scrapResults as $scrapResult )
            {
                $resultHash = strtolower( "{$scrapResult->originalTitle} ({$scrapResult->productionYear})" );
                if( !isset( $result->variables["results"][$resultHash] ) )
                {
                    $mergedResult = new \stdClass;
                    $mergedResult->originalTitle = $scrapResult->originalTitle;
                    $mergedResult->title = $scrapResult->title;
                    $mergedResult->thumbnail = $scrapResult->thumbnail;
                    $mergedResult->productionYear = $scrapResult->productionYear;
                    $mergedResult->{"id_$identifier"} = $scrapResult->id;
                    $mergedResult->{"url_$identifier"} = $scrapResult->url;
                    $result->variables["results"][$resultHash] = $mergedResult;
                }
                else
                {
                    $result->variables["results"][$resultHash]->{"id_$identifier"} = $scrapResult->id;
                    $result->variables["results"][$resultHash]->{"url_$identifier"} = $scrapResult->url;
                }
            }

            foreach( $result->variables["results"] as $mergedResult )
            {
                $mergedResult->generateUrl = sprintf(
                    "/nfo/movie/generate/%s/%s/%s",
                    urlencode( $query ),
                    isset( $mergedResult->id_allocine ) ? $mergedResult->id_allocine : 'none',
                    isset( $mergedResult->id_tmdb ) ? $mergedResult->id_tmdb : 'none'
                );
            }
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
        $result->variables['updateUrl'] = '/nfo/movie/update-info';

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
        $result->variables['nfo'] = $nfoWriter->get();
        return $result;
    }

    /**
     * Saves a NFO
     *
     * @param string $this->folder
     * @param string $this->info
     *
     * @return ezcMvcResult
     */
    public function doNfoSave()
    {
        $result = new \ezcMvcResult();

        $basepath =
            ezcConfigurationManager::getInstance()->getSetting( 'movies', 'GeneralSettings', 'SourcePath' ) .
            DIRECTORY_SEPARATOR .
            $this->folder . DIRECTORY_SEPARATOR;
        $nfoFilepath = "{$basepath}{$this->folder}.nfo";
        $posterFilepath = "{$basepath}{$this->folder}.tbn";
        $fanartFilepath = "{$basepath}{$this->folder}-fanart.jpg";
        $trailerFilepath = "{$basepath}{$this->folder}-trailer.flv";

        $result->variables['filepath_nfo'] = $nfoFilepath;
        $result->variables['filepath_poster'] = $posterFilepath;
        $result->variables['filepath_fanart'] = $fanartFilepath;
        $result->variables['filepath_trailer'] = $trailerFilepath;

        $nfoWriter = new NfoWriter( $this->info );
        $nfoWriter->write( $nfoFilepath );
        $nfoWriter->downloadTrailer( $trailerFilepath );
        $nfoWriter->downloadMainPoster( $posterFilepath );
        $nfoWriter->downloadMainFanart( $fanartFilepath );

        return $result;
    }
}
?>