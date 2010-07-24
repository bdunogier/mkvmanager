<?php
require_once 'ezc/Base/base.php';
spl_autoload_register( array( 'ezcBase', 'autoload' ) );
ezcBase::addClassRepository( './lib', './lib/autoload' );

$options = new ezcBaseAutoloadOptions;
$options->debug = true;
ezcBase::setOptions( $options );

$db = ezcDbFactory::create( 'sqlite:///home/bertrand/gitmisc/mkvmanager/tmp/mergequeue.db' );
ezcDbInstance::set( $db );
?>