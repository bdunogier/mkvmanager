<?php
/**
 * TVShow class
 *
 * @version $Id$
 * @copyright 2011
 */
class TVShow
{
    public function __construct( $name )
    {
        $this->name = $name;
        $this->systemName = preg_replace( '/[^a-z0-9]/', '', $this->name );
    }

    public $name;
    public $systemName;
}
?>