<?php
/**
* Background process output monitoring test
* Execute the application and redirect the output to a temporary file
* Follow that temporary file and process the output
*/

$tempFile = "tmp/" . uniqid();
exec( "loop > /tmp/$tempFile &", $output, $return );
var_dump( $return );
?>