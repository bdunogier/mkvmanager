<?php
/**
 * File containing the mm\Info\Image class
 */

namespace mm\Info;

class Image
{
    public function __construct( $type = 'poster', $fullUrl = '' )
    {
        $this->type = $type;
        $this->fullUrl = $fullUrl;
    }

    public function __toString()
    {
        return $this->fullUrl;
    }

    /**
     * Image type (fanart/poster)
     */
    public $type;

    /**
     * HTTP URL to the full image
     * @var string
     */
    public $fullUrl;

    /**
     * public $thumbnailURL
     * @var string
     */
    public $thumbnailUrl;

    /**
     * Image width
     * @var int
     */
    public $width;

    /**
     * Image height
     * @var int
     */
    public $height;

    public static function __set_state( $array )
    {
        $object = new self;
        foreach ($array as $property => $value )
        {
            $object->$property = $value;
        }
        return $object;
    }
}
?>