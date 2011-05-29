<?php
namespace mm\Info;

/**
 * A movie trailer
 */
class Trailer
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

    public function __toString()
    {
        return $this->url;
    }

    /**
     * The trailer's title
     * @var string
     */
    public $title;

    /**
     * HTTP Link
     * @var string
     */
    public $url;

    /**
     * Language
     * @var string
     */
    public $language;
}
?>