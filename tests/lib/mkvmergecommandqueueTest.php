<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '../../lib/mkvmerge/commandqueue.php';
require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '../../autoload.php';
echo ini_get('include_path');

class lib_MKVMergeCommandQueueTest extends PHPUnit_Framework_TestCase
{

	public function setUp()
	{
		$db = ezcDbFactory::create( 'sqlite://:memory' );
		ezcDbInstance::set( $db );
		$db->query( 'CREATE TABLE commands (time INTEGER PRIMARY KEY, command TEXT, pid INTEGER, status INTEGER, message TEXT)' );
	}

	public function testGetNextCommand()
	{
		$db = ezcDbInstance::get();

		// nothing in the table
		$ret = MKVMergeCommandQueue::getNextCommand();
		self::assertFalse( $ret );

		// insert a command
		$q = $db->createInsertQuery();
		$q->insertInto( 'commands' )
		  ->set( 'time', $q->bindValue( 1279829112 ) )
		  ->set( 'command', $q->bindValue( 'mkvmerge -o "/media/storage/NEFARIAN/Movies/Nausicaa Of The Valley Of The Wind (1994)/Nausicaa Of The Valley Of The Wind (1994).mkv"  "--language" "1:eng" "--default-track" "1:no" "--forced-track" "1:no" "--display-dimensions" "1:80x43" "--language" "2:jpn" "--default-track" "2:yes" "--forced-track" "2:no" "--language" "3:eng" "--default-track" "3:no" "--forced-track" "3:no" "--language" "4:eng" "--default-track" "4:yes" "--forced-track" "4:no" "-a" "2,3" "-d" "1" "-s" "4" "-T" "--no-global-tags" "--no-chapters" "/home/download/downloads/complete/Movies/Nausicaa Of The Valley Of The Wind (1994)/Nausicaa Of The Valley Of The Wind (1994).mkv" "--track-order" "0:4,0:1,0:2,0:3"' ) )
		  ->set( 'status', $q->bindValue( 0 ) )
		  ->set( 'message', $q->bindValue( '' ) );
		$sth = $q->prepare();
		$sth->execute();

		$q = $db->createSelectQuery();
		$q->select( '*' )->from( 'commands' );
		$sth = $q->prepare();
		$sth->execute();

		$ret = MKVMergeCommandQueue::getNextCommand();
		self::assertType( 'array', $ret );
	}

	public function tearDown()
	{
		ezcDbinstance::resetDefault();
	}
}

?>