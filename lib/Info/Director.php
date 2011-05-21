<?php
namespace mm\Info;

class Director extends Person
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
}
?>