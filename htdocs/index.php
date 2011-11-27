<?php
// Include the configuration file
include '../config.php';
include '../check.php';

// Instantiate the dispatcher configuration object.
$config = new mmMvcConfiguration();

// Send the configuration to the dispatcher, and run it.
try {
	$dispatcher = new ezcMvcConfigurableDispatcher( $config );
	$dispatcher->run();
} catch( Exception $e ) {
	echo "<span style=\"color:red\">Exception: " . $e->getMessage() . "</span><br>";
	echo "<pre>" . $e->getTraceAsString()  . "</pre>";
}
?>
