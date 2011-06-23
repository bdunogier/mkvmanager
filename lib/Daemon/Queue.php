<?php
namespace mm\Daemon;
use ezcPersistentSessionInstance;
use ezcPersistentObjectAlreadyPersistentException;
use ezcBaseException;

/**
* Represents the daemon operations queue
*
* // add an operation to the queue
* mm\Daemon\Queue::add( new mm\Operations\ExampleOperation );
*
* // retrieve the next operation
* mm\Daemon\Queue::getNextItem();
*/
class Queue
{
    /**
     * Adds the operation $operation to the queue
     * @param string $operation
     * @return QueueItem
     */
    public static function add( BackgroundOperation $operation )
    {
        $queueItem = new QueueItem( $operation );

        try {
            $queueItem->store();
        } catch( ezcPersistentObjectAlreadyPersistentException  $e ) {
            // @todo Add status check
            throw new AlreadyQueuedException( $queueItem );
        }

        return $queueItem;
    }

    /**
     * Returns the next pending queue item
     *
     * @return QueueItem
     */
    public static function getNextItem()
    {
        $session = ezcPersistentSessionInstance::get();

        $query = $session->createFindQuery( 'mm\\Daemon\\QueueItem' );
        $query->where( $query->expr->eq( 'status', $query->bindValue( QueueItem::STATUS_PENDING ) ) )
              ->limit( 1 );
        $pendingOperations = $session->find( $query );

        if ( count( $pendingOperations ) == 1 )
            return array_pop( $pendingOperations );
        else
            return false;
    }

    /**
     * Fetches the currently running items
     *
     * @param int status one of QueueItem::STATUS_ARCHIVED, QueueItem::STATUS_DONE, QueueItem::STATUS_ERROR, QueueItem::STATUS_PENDING, QueueItem::STATUS_RUNNING
     * @param string $type
     *
     * @return array( mm\Daemon\QueueItem )
     */
    public static function fetchItems( $status, $type = null)
    {
        if ( !in_array( $type, array( QueueItem::STATUS_ARCHIVED, QueueItem::STATUS_DONE, QueueItem::STATUS_ERROR, QueueItem::STATUS_PENDING, QueueItem::STATUS_RUNNING ) ) )
            throw new ezcBaseValueException( 'type', $type );
        $session = ezcPersistentSessionInstance::get();
        $q = $session->createFindQuery( 'mm\Daemon\QueueItem' );
        $q->where( $q->expr->eq( 'status', $status ) )
          ->orderBy( 'create_time' );
        if ( $type !== null )
            $q->where( $q->expr->eq( 'type', $type ) );
        return $session->find( $q );
    }
}

class AlreadyQueuedException extends ezcBaseException
{
    public function __construct( $command )
    {
        parent::__construct( "Command already queued: $command" );
    }
}
?>