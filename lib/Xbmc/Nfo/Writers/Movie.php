<?php
/**
 * File containing the mm\Xbmc\Nfo\Writers\Movie class.
 *
 * @version $Id$
 * @copyright 2011
 */

/**
 * Movie NFO Generator for XBMC
 *
 * Format documentation:
 * http://wiki.xbmc.org/index.php?title=Import_-_Export_Library#Movies
 *
 * Uses an Info\Movie\Details object to generate an XBMC NFO
 */
namespace mm\Xbmc\Nfo\Writers;
use \ezcBaseFileNotFoundException as ezcBaseFileNotFoundException;
use \ezcBaseFilePermissionException as ezcBaseFilePermissionException;
use \ezcBaseFileException as ezcBaseFileException;

class Movie
{
    /**
     * Constructs a new Movie NFO writer based on the information object $info
     *
     * @param mm\Info\Movie\Details $info
     */
    public function __construct( \mm\Info\Movie\Details $info )
    {
        $this->info = $info;
    }

    /**
     * Returns the NFO as a string
     */
    public function get()
    {
        $this->generateXml();
        return $this->getDom()->saveXML();
    }

    /**
     * Writes the NFO to the file $filename
     * @param string $filename
     *
     * @throws ezcBaseFileNotFoundException if $filename's directory doesn't exist
     * @throws ezcBaseFilePermissionException if $filename's directory can't be written to
     */
    public function write( $filename )
    {
        $directory = dirname( $filename );

        if ( !file_exists( $directory ) )
        {
            throw new ezcBaseFileNotFoundException( $directory );
        }

        if (!is_writeable( $directory ) )
        {
            throw new ezcBaseFilePermissionException( $directory, ezcBaseFileException::WRITE );
        }

        $this->generateXml();

        file_put_contents( $filename, $this->get() );
    }

    /**
     * Returns the DOMDocument from the SimpleXMLElement $this->xml
     * Used to format the XML properly
     *
     * @return DOMDocument
     */
    private function getDom()
    {
        $dom = dom_import_simplexml( $this->xml )->ownerDocument;
        $dom->formatOutput = true;
        return $dom;
    }

    /**
     * Generates the XML structure from $this->info
     */
    private function generateXml()
    {
        if ( $this->xml instanceof SimpleXMLElement )
            return;

        $xml = simplexml_load_string( '<?xml version="1.0" encoding="utf-8" standalone="yes"?><movie/>',
            'SimpleXMLElement', LIBXML_NOEMPTYTAG );

        $xml->title = $this->info->title;
        $xml->originalTitle = $this->info->originalTitle;
        $xml->set = '';
        $xml->sorttitle = '';

        if ( count( $this->info->trailers ) )
        {
            $xml->trailer = $this->info->trailers[0]->url;
        }

        if ( count( $this->info->genre ) )
        {
            foreach( $this->info->genre as $genre )
            {
                $xml->genres->genre[] = (string)$genre;
            }
        }

        $xml->rating = $this->info->score;
        $xml->year = $this->info->productionYear;
        $xml->outline = $this->info->plot;
        $xml->plot = $this->info->synopsis;
        $xml->tagline = '';
        $xml->runtime = $this->info->runtime;

        if ( count( $this->info->actors ) )
        {
            foreach( $this->info->actors as $index => $actor )
            {
                $xml->actors->actor[$index]->name = $actor->name;
                $xml->actors->actor[$index]->role = $actor->role;
                $xml->actors->actor[$index]->thumb = $actor->image;
            }
        }

        if ( count( $this->info->directors ) )
        {
            $directors = array();
            foreach( $this->info->directors as $director )
            {
                $directors[] = $director->name;
            }
            $xml->director = implode( ', ', $directors );
        }

        if ( count( $this->info->posters ) )
        {
            foreach( $this->info->posters as $index => $poster )
            {
                if ( $poster !== null )
                    $xml->thumbs->thumb[] = (string)$poster;
            }
        }

        if ( count( $this->info->fanarts ) )
        {
            foreach( $this->info->fanarts as $index => $fanart )
            {
                if ( $fanart !== null )
                    $xml->fanarts->thumb[] = (string)$fanart;
            }
        }

        $this->xml = $xml;
    }

    /**
     * Downloads and saves the main movie poster to $filepath
     *
     * @param string $filepath
     * @return bool true if the file was written successfully
     */
    public function downloadMainPoster( $filepath )
    {
        return $this->download( (string)$this->info->posters[0], $filepath );
    }

    /**
     * Downloads and saves the main movie fanart to $filepath
     *
     * @param string $filepath
     * @return bool true if the file was written successfully
     */
    public function downloadMainFanart( $filepath )
    {
        return $this->download( (string)$this->info->fanarts[0], $filepath );
    }

    /**
     * Downloads and saves the movie trailer to $filepath
     *
     * @param string $filepath
     * @return bool true if the file was written successfully
     */
    public function downloadTrailer( $filepath )
    {
        return $this->download( (string)$this->info->trailers[0], $filepath );
    }

    /**
     * Downloads and saves $file to $filepath
     *
     * @param string $file
     * @param string $targetpath
     * @return bool true if the file was written successfully
     */
    private function download( $file, $targetpath )
    {
        return copy( $file, $targetpath );
    }

    /**
     * Information object
     * @var mm\Info\Movie\Details $info
     */
    private $info;

    /**
     * NFO XML
     * @var SimpleXMLElement
     */
    private $xml;
}
?>