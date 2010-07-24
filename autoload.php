<?php
require_once 'ezc/Base/base.php';
spl_autoload_register( array( 'ezcBase', 'autoload' ) );
ezcBase::addClassRepository( './lib', './lib/autoload' );

$options = new ezcBaseAutoloadOptions;
$options->debug = true;
ezcBase::setOptions( $options );

$rootdir = dirname( __FILE__ );
$db = ezcDbFactory::create( "sqlite://$rootdir/tmp/mergequeue.db" );
ezcDbInstance::set( $db );
?>