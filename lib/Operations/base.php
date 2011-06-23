<?php
/**
 * File containg the mm\Operations\Base abstract class
 * @package mm
 * @subpackage Operations
 */
namespace mm\Operations;
use mm\Daemon\QueueItem;

/**
 * Provides the abstract methods for an operation
 */
abstract class Base
{
    /**
     * @var mm\Daemon\QueueItem
     */
    private $queueItem;

    /**
     * Returns the operation type, usually the class name with the namespace stripped
     * @return string
     */
    public function getType()
    {
        return substr( get_class( $this ), strrpos( get_class( $this ), '\\' ) + 1 );
    }

    /**
     * Sets the queue item containing the operation
     * @param mm\Daemon\QueueItem
     */
    public function setQueueItem( QueueItem $queueItem )
    {
        $this->queueItem = $queueItem;
    }

    /**
     * Updates the queue item informations
     *
     * @param int $progress The progress, from 0 to 100
     * @param string $message The operation message
     */
    protected function updateQueueItem( $progress, $message = '' )
    {
        $this->queueItem->message = $message;
        $this->queueItem->progress = $progress;
        $this->queueItem->update();
    }
}
?>