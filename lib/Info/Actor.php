<?php
namespace mm\Info;

class Actor extends Person
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
     * The person's role in the media
     * @var string
     */
    public $role;
}
?>