<?php

class lib_mkvmerge_MKVMergeCommandTest extends PHPUnit_Framework_TestCase
{
	public function __construct()
	{
		$this->backupGlobals = false;
		$this->backupStaticAttributes = false;
	}

	public function testConstruct()
	{
		$command = 'mkvmerge -o "/media/storage/NEFARIAN/Movies/Nausicaa Of The Valley Of The Wind (1994)/Nausicaa Of The Valley Of The Wind (1994).mkv" "--language" "1:eng" "--default-track" "1:no" "--forced-track" "1:no" "--display-dimensions" "1:80x43" "--language" "2:jpn" "--default-track" "2:yes" "--forced-track" "2:no" "--language" "3:eng" "--default-track" "3:no" "--forced-track" "3:no" "--language" "4:eng" "--default-track" "4:yes" "--forced-track" "4:no" "-a" "2,3" "-d" "1" "-s" "4" "-T" "--no-global-tags" "--no-chapters" "/home/download/downloads/complete/Movies/Nausicaa Of The Valley Of The Wind (1994)/Nausicaa Of The Valley Of The Wind (1994).mkv" "--track-order" "0:4,0:1,0:2,0:3"';
		$mkvCommand = new MKVMergeCommand( $command );
		self::assertEquals( 'movie', $mkvCommand->conversionType );
		self::assertEquals( "Nausicaa Of The Valley Of The Wind (1994)", $mkvCommand->title );
		self::assertEquals( "/media/storage/NEFARIAN/Movies/Nausicaa Of The Valley Of The Wind (1994)", $mkvCommand->target );
		self::assertNull( $mkvCommand->showName );
		self::assertNull( $mkvCommand->episodeName );
		self::assertEquals( "/media/aggregateshares/Movies/", $mkvCommand->linkTarget );
	}
}

?>