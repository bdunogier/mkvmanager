<?php
namespace mm\Info\Movie;

class Details extends SearchResult
{
    public static function __set_state( $array )
    {
        $object = new self;
        foreach ($array as $property => $value )
        {
            $object->$property = $value;
        }
        return $object;
    }

    /**
     * Swaps $itemOne with $itemTwo for property $field
     *
     * @param string $field
     * @param int $itemOne
     * @param int $itemTwo
     *
     * @return void
     *
     * @throws ezcBasePropertyNotFoundException if the given $field is not a swappable property
     */
    public function swap( $field, $itemOne, $itemTwo )
    {
        if ( !in_array( $field, array( 'trailers', 'posters', 'fanarts' ) ) )
            throw new ezcBasePropertyNotFoundException( $field );

        $property =& $this->$field;
        if ( $property[$itemOne] == $property[$itemTwo] )
            return;

        $tmpTrailer = $property[$itemOne];
        $property[$itemOne] = $property[$itemTwo];
        $property[$itemTwo] = $tmpTrailer;
    }

    /**
     * Removes the item #$item from the property $field
     *
     * @param string $field
     * @param int $item
     *
     * @return void
     *
     * @throws ezcBasePropertyNotFoundException if the given $field is not a swappable property
     */
    public function remove( $field, $item )
    {
        if ( !in_array( $field, array( 'posters', 'fanarts' ) ) )
            throw new ezcBasePropertyNotFoundException( $field );

        $property =& $this->$field;

        if ( $item >= count( $property ) )
            return;

        // overwrite all items with the next from the requested one
        for( $i = $item; $i < count( $property ) - 1; $i++ )
        {
            $property[$i] = $property[$i+1];
        }
        unset( $property[$i] );
    }

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

    /**
     * Movie duration, in minutes
     * @var int
     */
    public $runtime;
}
?>