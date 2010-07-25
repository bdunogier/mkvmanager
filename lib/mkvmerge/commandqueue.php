<?php

/**
 * File containing the MkvMergeCommandQueue class
 *
 * @version $Id$
 * @copyright 2010
 */

/**
 *
 *
 */
class MKVMergeCommandQueue
{

	/**
	 * Constructor
	 */
	function __construct()
	{
		$this->db = ezcDbInstance::get();
	}

	/**
	 * Returns the next command to be executed
	 *
	 * @return MKVMergeCommand The command, or false if no waiting command
	 */
	public static function getNextCommand()
	{
		$db = ezcDbInstance::get();

		$q = $db->createSelectQuery();
		$q->select( 'time', 'command' )
		  ->from( 'commands' )
		  ->where( $q->expr->eq( 'status', self::STATUS_WAITING ) )
		  ->orderBy( 'time', $q::ASC )
		  ->limit( 1, 0 );
		$sth = $q->prepare();
		$sth->execute();

		$row = $sth->fetch( PDO::FETCH_ASSOC );
		if ( $row === false )
		{
			return false;
		}
		else
		{
			return new MKVMergeCommand( $row['command'], $row['time'] );
		}
	}

	const STATUS_WAITING = 0;
	const STATUS_ERROR = -1;
	const STATUS_DONE = 1;

	/**
	 * @var ezcDbHandler
	 */
	private $db;
}

?>