<?php
/**
 * File containing the tclMvcResultStatusError class
 *
 * @copyright Copyright (C) 2005-2010 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @version 1.1.3
 * @filesource
 * @package MvcTools
 */

/**
 * Generic handler for error statuses
 */
class mmMvcResultStatusError implements ezcMvcResultStatusObject
{
    public function process( ezcMvcResponseWriter $writer )
    {
        if ( $writer instanceof ezcMvcHttpResponseWriter )
            header( 'HTTP/1.1 500 Internal Server Error' );
    }
}
?>
