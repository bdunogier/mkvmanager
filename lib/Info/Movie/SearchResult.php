<?php
namespace mm\Info\Movie;

class SearchResult
{
    /**
     * The movie's original title
     * @var string
     */
    public $originalTitle;

    /**
     * The movie's title
     * @var string
     */
    public $title;

    /**
     * Link to the online movie details
     * @var string
     */
    public $link;

    /**
     * Movie poster thumbnail
     * @var string
     */
    public $thumbnail;

    /**
     * Id on the info scraper
     * @var string
     */
    public $id;

    /**
     * Year the movie was produced
     * @var string
     */
    public $productionYear;

    /**
     * Date the movie was released
     * @var string
     */
    public $releaseDate;

    /**
     * Year the movie was released
     * @var string
     */
    public $releaseYear;

    /**
     * Short list of directors (names only)
     * @var array(string)
     */
    public $directorsShort;

    /**
     * Short list of actors
     * @var array(string)
     */
    public $actorsShort;

    /**
     * URL to the website page for the movie
     * @var string
     */
    public $url;
}
?>