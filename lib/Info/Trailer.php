<?php
namespace mm\Info;

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