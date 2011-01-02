<?php
include 'config.php';

// check if media target folder is writeable
$storageDir = '/media/aggregateshares/';
if ( !is_writeable( $storageDir ) )
{
	echo "$storageDir can not be written to. Wrong user maybe ?\n";
	die();
}

$sudo = "sudo -u media";
while( $operation = mmMergeOperation::next() )
{
    $result = '';
	$return = '';

    // mark operation as running
    $operation->status = mmMergeOperation::STATUS_RUNNING;
    $operation->startTime = time();
    ezcPersistentSessionInstance::get()->update( $operation );

    $commandObject = $operation->commandObject;
    echo "[" . date('H:i:s') . "] Starting conversion of {$commandObject->conversionType} '{$commandObject->title}'\n";
    exec( $operation->command, $result, $return );
	echo "[" . date('H:i:s') . "] Conversion finished\n";

	$status = ( $result !== 0 ) ? -1 : 0;

	$operation->status = ( $status == 0 ) ? mmMergeOperation::STATUS_DONE : mmMergeOperation::STATUS_ERROR;
    $operation->message = $result;
    $operation->endTime = time();
    ezcPersistentSessionInstance::get()->update( $operation );

	unset( $result, $return );
}
?>