<?php
/**
 * File containing the ezcMvcResultStatusObject class
 *
 * @copyright Copyright (C) 2005-2010 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @version 1.1.3
 * @filesource
 * @package MvcTools
 */

/**
 * The interface that should be implemented by all special status objects.
 *
 * Statis objects are used to specify non-normal results from actions.
 * As an example that could be a "Authorization Required" status, an external
 * redirect etc.
 *
 *
 * @package MvcTools
 * @version 1.1.3
 */
class mmMvcResultStatusNotFound implements ezcMvcResultStatusObject
{
    public function process( ezcMvcResponseWriter $writer )
    {
        if ( $writer instanceof ezcMvcHttpResponseWriter )
            header( 'HTTP/1.0 404 Not Found' );
    }
}
?>
