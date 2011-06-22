<?php
/**
 * File containing the mm\Daemon\AsynchronousProgressBackgroundOperation class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/gnu_gpl GNU GPL v2
 * @version //autogentag//
 * @package mm
 * @subpackage Daemon
 */

/**
 * This interface is used to implement a runnable operation
 */
namespace mm\Daemon;

interface BackgroundOperation
{
    /**
     * Run the operation
     */
    public function run();

    /**
     * Resets the operation
     */
    public function reset();

    /**
     * The operation's title
     *
     * @return string
     */
    public function __toString();
}
?>