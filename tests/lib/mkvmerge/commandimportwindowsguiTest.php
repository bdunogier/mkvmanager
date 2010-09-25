<?php
class MKVMergeCommandImportWindowsGUITest extends PHPUnit_Framework_TestCase
{
	public function __construct()
	{
		$this->backupGlobals = false;
		$this->backupStaticAttributes = false;
	}

	public function testMovieWithSubfolder()
	{
		$command = '"D:\Program Files\MKVtoolnix\mkvmerge.exe" -o "F:\\Nausicaa Of The Valley Of The Wind (1994)\\Nausicaa Of The Valley Of The Wind (1994).mkv" "--language" "1:eng" "--default-track" "1:no" "--forced-track" "1:no" "--display-dimensions" "1:80x43" "--language" "2:jpn" "--default-track" "2:yes" "--forced-track" "2:no" "--language" "3:eng" "--default-track" "3:no" "--forced-track" "3:no" "--language" "4:eng" "--default-track" "4:yes" "--forced-track" "4:no" "-a" "2,3" "-d" "1" "-s" "4" "-T" "--no-global-tags" "--no-chapters" "X:\\complete\\Movies\\Nausicaa Of The Valley Of The Wind (1994)\\Nausicaa Of The Valley Of The Wind (1994).mkv" "--track-order" "0:4,0:1,0:2,0:3"';

		$convertedCommand = MKVMergeCommandImportWindowsGUI::convert( $command, 'NEFARIAN' );

		self::assertEquals( 'movie', $convertedCommand->conversionType );
		self::assertEquals( 'Nausicaa Of The Valley Of The Wind (1994)', $convertedCommand->title );
		self::assertEquals( '/media/storage/NEFARIAN/Movies/Nausicaa Of The Valley Of The Wind (1994)', $convertedCommand->target );
		self::assertEquals( '/media/aggregateshares/Movies/', $convertedCommand->linkTarget );
	}

	public function testMovieWithoutSubfolder()
	{
		$command = '"D:\Program Files\MKVtoolnix\mkvmerge.exe" -o "F:\\Nausicaa Of The Valley Of The Wind (1994).mkv" "--language" "1:eng" "--default-track" "1:no" "--forced-track" "1:no" "--display-dimensions" "1:80x43" "--language" "2:jpn" "--default-track" "2:yes" "--forced-track" "2:no" "--language" "3:eng" "--default-track" "3:no" "--forced-track" "3:no" "--language" "4:eng" "--default-track" "4:yes" "--forced-track" "4:no" "-a" "2,3" "-d" "1" "-s" "4" "-T" "--no-global-tags" "--no-chapters" "X:\\complete\\Movies\\Nausicaa Of The Valley Of The Wind (1994)\\Nausicaa Of The Valley Of The Wind (1994).mkv" "--track-order" "0:4,0:1,0:2,0:3"';

		$convertedCommand = MKVMergeCommandImportWindowsGUI::convert( $command, 'NEFARIAN' );

		self::assertEquals( 'movie', $convertedCommand->conversionType );
		self::assertEquals( 'Nausicaa Of The Valley Of The Wind (1994)', $convertedCommand->title );
		self::assertEquals( '/media/storage/NEFARIAN/Movies/Nausicaa Of The Valley Of The Wind (1994)', $convertedCommand->target );
		self::assertEquals( '/media/aggregateshares/Movies/', $convertedCommand->linkTarget );
	}

	public function testTVShowWithSubfolder()
	{
		$command = '"D:\Program Files\MKVtoolnix\mkvmerge.exe" -o "F:\\Weeds\\Weeds - 6x02 - Felling and Swamping.mkv"  "--sub-charset" "0:ISO-8859-1" "--language" "0:fre" "--default-track" "0:yes" "--forced-track" "0:no" "-s" "0" "-D" "-A" "-T" "--no-global-tags" "--no-chapters" "X:\\complete\\TV\\Sorted\\Weeds\\Weeds - 6x02 - Felling and Swamping.ass" "--language" "1:eng" "--default-track" "1:yes" "--forced-track" "1:no" "--display-dimensions" "1:1280x720" "--language" "2:eng" "--track-name" "2:English" "--default-track" "2:yes" "--forced-track" "2:no" "-a" "2" "-d" "1" "-S" "-T" "--no-global-tags" "--no-chapters" "X:\\complete\\TV\\Sorted\\Weeds\\Weeds - 6x02 - Felling and Swamping.mkv" "--track-order" "0:0,1:1,1:2"';

		$convertedCommand = MKVMergeCommandImportWindowsGUI::convert( $command, 'NEFARIAN' );

		self::assertEquals( 'tvshow', $convertedCommand->conversionType );
		self::assertEquals( 'Weeds - 6x02 - Felling and Swamping', $convertedCommand->title );
		self::assertEquals( '/media/storage/NEFARIAN/TV Shows/Weeds/Weeds - 6x02 - Felling and Swamping.mkv', $convertedCommand->target );
		self::assertEquals( '/media/aggregateshares/TV Shows/Weeds/', $convertedCommand->linkTarget );
	}

	public function testTVShowWithoutSubfolder()
	{
		$command = '"D:\Program Files\MKVtoolnix\mkvmerge.exe" -o "F:\\Weeds - 6x02 - Felling and Swamping.mkv"  "--sub-charset" "0:ISO-8859-1" "--language" "0:fre" "--default-track" "0:yes" "--forced-track" "0:no" "-s" "0" "-D" "-A" "-T" "--no-global-tags" "--no-chapters" "X:\\complete\\TV\\Sorted\\Weeds\\Weeds - 6x02 - Felling and Swamping.ass" "--language" "1:eng" "--default-track" "1:yes" "--forced-track" "1:no" "--display-dimensions" "1:1280x720" "--language" "2:eng" "--track-name" "2:English" "--default-track" "2:yes" "--forced-track" "2:no" "-a" "2" "-d" "1" "-S" "-T" "--no-global-tags" "--no-chapters" "X:\\complete\\TV\\Sorted\\Weeds\\Weeds - 6x02 - Felling and Swamping.mkv" "--track-order" "0:0,1:1,1:2"';

		$convertedCommand = MKVMergeCommandImportWindowsGUI::convert( $command, 'NEFARIAN' );

		self::assertEquals( 'tvshow', $convertedCommand->conversionType );
		self::assertEquals( 'Weeds - 6x02 - Felling and Swamping', $convertedCommand->title );
		self::assertEquals( '/media/storage/NEFARIAN/TV Shows/Weeds/Weeds - 6x02 - Felling and Swamping.mkv', $convertedCommand->target );
		self::assertEquals( '/media/aggregateshares/TV Shows/Weeds/', $convertedCommand->linkTarget );
	}
}

?>