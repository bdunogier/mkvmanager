<?php
/**
 * File containing the mm\Daemon\BackgroundOperation class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/gnu_gpl GNU GPL v2
 * @version //autogentag//
 * @package mm
 * @subpackage Daemon
 */

/**
 * A runnable operation that doesn't update its progress while running, but
 * requires an external call to its progress() method
 */
namespace mm\Daemon;

interface NoProgressBackgroundOperation extends BackgroundOperation
{
    /**
     * Reports the operation progress, as an integer (1-100)
     *
     * @return integer
     */
    public function progress();
}
?>