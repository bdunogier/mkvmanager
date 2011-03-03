<?php
/**
 * TVShow class
 *
 * @version $Id$
 * @copyright 2011
 */
class TVShowFolder extends TVShow
{
    public function __construct( $name, $parentFolder )
    {
        parent::__construct( $name );
        $this->folder = "{$parentFolder}/{$name}";
    }

    public $folder;
}
?>