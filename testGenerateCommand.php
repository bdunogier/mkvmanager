<?php
include 'lib/mkvmerge/command_generator.php';
include 'lib/mkvmerge/command.php';

$filename = 'How I Met Your Mother - 6x07 - Canning Randy.mkv';
$command = MKVMergeTVCommandGenerator::generate( $filename );

echo "Command:\n" . (string)$command->command;
?>