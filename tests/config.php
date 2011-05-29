<?php
/**
 * Tests configurationfile
 */
define( 'ROOT', getcwd() );
ini_set( 'include_path', "/usr/share/php/ezc:/usr/share/php" . ROOT );

require 'Base/ezc_bootstrap.php';

$options = new ezcBaseAutoloadOptions( array( 'debug' => true ) );
ezcBase::setOptions( $options );

// Add the class repository containing our application's classes. We store
// those in the /lib directory and the classes have the "tcl" prefix.
ezcBase::addClassRepository( ROOT . "/lib", ROOT . "/lib/autoload", 'mm' );

class mmLazySettingsConfiguration implements ezcBaseConfigurationInitializer
{
    public static function configureObject( $cfgManager )
    {
        $cfgManager->init( 'ezcConfigurationIniReader', ROOT . "/tests/config" );
    }
}

ezcBaseInit::setCallback(
    'ezcInitConfigurationManager',
    'mmLazySettingsConfiguration'
);

?>