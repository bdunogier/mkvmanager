<?php

class lib_mkvmerge_MKVMergeCommandImportWindowsGUITest extends PHPUnit_Framework_TestCase
{
	public function __construct()
	{
		$this->backupGlobals = false;
		$this->backupStaticAttributes = false;
	}

	public function testConvert()
	{
		$command = '"D:\Program Files\MKVtoolnix\mkvmerge.exe" -o "F:\\Nausicaa Of The Valley Of The Wind (1994)\\Nausicaa Of The Valley Of The Wind (1994).mkv" "--language" "1:eng" "--default-track" "1:no" "--forced-track" "1:no" "--display-dimensions" "1:80x43" "--language" "2:jpn" "--default-track" "2:yes" "--forced-track" "2:no" "--language" "3:eng" "--default-track" "3:no" "--forced-track" "3:no" "--language" "4:eng" "--default-track" "4:yes" "--forced-track" "4:no" "-a" "2,3" "-d" "1" "-s" "4" "-T" "--no-global-tags" "--no-chapters" "X:\\complete\\Movies\\Nausicaa Of The Valley Of The Wind (1994)\\Nausicaa Of The Valley Of The Wind (1994).mkv" "--track-order" "0:4,0:1,0:2,0:3"';
		$expected = new MKVMergeCommand( 'mkvmerge -o "/media/storage/NEFARIAN/Movies/Nausicaa Of The Valley Of The Wind (1994)/Nausicaa Of The Valley Of The Wind (1994).mkv" "--language" "1:eng" "--default-track" "1:no" "--forced-track" "1:no" "--display-dimensions" "1:80x43" "--language" "2:jpn" "--default-track" "2:yes" "--forced-track" "2:no" "--language" "3:eng" "--default-track" "3:no" "--forced-track" "3:no" "--language" "4:eng" "--default-track" "4:yes" "--forced-track" "4:no" "-a" "2,3" "-d" "1" "-s" "4" "-T" "--no-global-tags" "--no-chapters" "/home/download/downloads/complete/Movies/Nausicaa Of The Valley Of The Wind (1994)/Nausicaa Of The Valley Of The Wind (1994).mkv" "--track-order" "0:4,0:1,0:2,0:3"' );

		self::assertEquals( $expected, MKVMergeCommandImportWindowsGUI::convert( $command, 'NEFARIAN' ) );
	}
}

?>