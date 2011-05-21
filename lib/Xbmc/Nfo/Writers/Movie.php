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
        return $this->xml->asXML();
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
        $this->xml->asXML( $filename );
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
            foreach( $this->info->trailers as $index => $trailer )
            {
                $xml->trailers->trailer[$index] = $trailer->url;
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
                $xml->thumbs->thumb[$index] = $poster;
            }
        }

        if ( count( $this->info->fanarts ) )
        {
            foreach( $this->info->fanarts as $index => $fanart )
            {
                $xml->fanarts->thumb[$index] = $fanart;
            }
        }

        $this->xml = $xml;
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