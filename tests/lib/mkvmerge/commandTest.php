<?php

class lib_mkvmerge_MKVMergeCommandTest extends PHPUnit_Framework_TestCase
{
	public function __construct()
	{
		$this->backupGlobals = false;
		$this->backupStaticAttributes = false;
	}

	public function setUp()
	{
	}

	public function testMovieWithSubfolder()
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

	/**
	 * Tests the __toString return values
	 *
	 * 1. Default values
	 * 2. With link + no message
	 * 3. With no link + message
	 * 4. With link + message
	 **/
	public function testToString()
	{
		$commandString = 'mkvmerge -o "/media/storage/NEFARIAN/Movies/Nausicaa Of The Valley Of The Wind (1994)/Nausicaa Of The Valley Of The Wind (1994).mkv" "--language" "1:eng" "--default-track" "1:no" "--forced-track" "1:no" "--display-dimensions" "1:80x43" "--language" "2:jpn" "--default-track" "2:yes" "--forced-track" "2:no" "--language" "3:eng" "--default-track" "3:no" "--forced-track" "3:no" "--language" "4:eng" "--default-track" "4:yes" "--forced-track" "4:no" "-a" "2,3" "-d" "1" "-s" "4" "-T" "--no-global-tags" "--no-chapters" "/home/download/downloads/complete/Movies/Nausicaa Of The Valley Of The Wind (1994)/Nausicaa Of The Valley Of The Wind (1994).mkv" "--track-order" "0:4,0:1,0:2,0:3"';
		$command = new MKVMergeCommand( $commandString );

		// 1. Default values
		self::assertNotContains( '; ln -s ', (string)$command );
		self::assertNotContains( '; echo "Done converting', (string)$command );

		// 2. With link + no message
		$command->appendSymLink = true;
		$command->appendDoneText = false;
		self::assertContains( '; ln -s ', (string)$command );
		self::assertNotContains( '; echo "Done converting', (string)$command );

		// 3. With no link + message
		$command->appendSymLink = false;
		$command->appendDoneText = true;
		self::assertNotContains( '; ln -s ', (string)$command );
		self::assertContains( '; echo "Done converting', (string)$command );

		// 4. With link + message
		$command->appendSymLink = true;
		$command->appendDoneText = true;
		self::assertContains( '; ln -s ', (string)$command );
		self::assertContains( '; echo "Done converting', (string)$command );
	}

	public function testGetFiles()
	{
		$commandString = 'mkvmerge -o "/media/storage/NEFARIAN/Movies/Lock Stock and Two Smoking Barrels (1998)/Lock Stock and Two Smoking Barrels (1998).mkv" "--language" "1:eng" "--default-track" "1:no" "--forced-track" "1:no" "--display-dimensions" "1:640x347" "--language" "2:eng" "--track-name" "2:DTS 5.1 1509kbps" "--default-track" "2:yes" "--forced-track" "2:no" "--language" "3:fre" "--default-track" "3:no" "--forced-track" "3:no" "--language" "4:ger" "--default-track" "4:no" "--forced-track" "4:no" "--language" "5:rum" "--default-track" "5:no" "--forced-track" "5:no" "--language" "6:spa" "--default-track" "6:no" "--forced-track" "6:no" "--language" "7:dut" "--default-track" "7:no" "--forced-track" "7:no" "--language" "8:eng" "--forced-track" "8:no" "-a" "2" "-d" "1" "-s" "3,4,5,6,7,8" "--attachments" "1,2,3" "-T" "--no-global-tags" "--no-chapters" "/home/download/downloads/complete/Movies/Lock Stock and Two Smoking Barrels (1998)/Lock Stock and Two Smoking Barrels (1998).mkv" "--language" "0:fre" "--default-track" "0:yes" "--forced-track" "0:no" "-s" "0" "-D" "-A" "-T" "--no-global-tags" "--no-chapters" "/home/download/downloads/complete/Movies/Lock Stock and Two Smoking Barrels (1998)/Lock Stock and Two Smoking Barrels (1998).srt" "--track-order" "0:1,0:2,0:3,0:4,0:5,0:6,0:7,0:8,1:0"; ln -s "/media/storage/NEFARIAN/Movies/Lock Stock and Two Smoking Barrels (1998)" "/media/aggregateshares/Movies/"';
		$command = new MKVMergeCommand( $commandString );

		$this->assertEquals( array( '/home/download/downloads/complete/Movies/Lock Stock and Two Smoking Barrels (1998)/Lock Stock and Two Smoking Barrels (1998).mkv' ),
			$command->videoFiles );
		$this->assertEquals( array( '/home/download/downloads/complete/Movies/Lock Stock and Two Smoking Barrels (1998)/Lock Stock and Two Smoking Barrels (1998).srt'),
			$command->subtitleFiles );
	}
}

?>