<?php
namespace mm\Info\Movie;

class Details extends SearchResult
{
    /**
     * Short movie summary
     * @var string
     */
    public $plot;

    /**
     * Full movie summary
     * @var string
     */
    public $synopsis;

    /**
     * Movie genre(s)
     * @var array(string)
     */
    public $genre;

    /**
     * Movie score, out of 10
     * @var float
     */
    public $score;

    /**
     * Movie trailers
     * @var array(mm\Info\Trailer)
     */
    public $trailers = array();

    /**
     * Movie posters
     * @var array(string)
     */
    public $posters = array();

    /**
     * Movie fanarts
     * @var array(string)
     */
    public $fanarts = array();

    /**
     * Movie actors, full
     * @var array(mm\Info\Actor)
     */
    public $actors = array();

    /**
     * Movie directors, full
     * @var array(mm\Info\Director
     */
    public $directors = array();
}
?>