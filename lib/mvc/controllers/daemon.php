<?php
namespace mm\Mvc\Controllers;
use mm\Daemon\QueueItem;
use mm\Daemon\Queue;
use ezcMvcController, ezcMvcResult;
use stdClass;

class Daemon extends ezcMvcController
{
    /**
     * Returns the queue contents with status $status, possibly filtered on $type
     *
     * @param string $status
     * @param string $type
     *
     * @return ezcMvcResult
     */
    public function doQueueContents()
    {
        switch( $this->status )
        {
            case 'archived': $status = QueueItem::STATUS_ARCHIVED; break;
            case 'done':     $status = QueueItem::STATUS_DONE; break;
            case 'error':    $status = QueueItem::STATUS_ERROR; break;
            case 'pending':  $status = QueueItem::STATUS_PENDING; break;
            case 'running':  $status = QueueItem::STATUS_RUNNING; break;
            default: $status = null;
        }

        if ( isset( $this->type ) )
        {
            $type = $this->type;
        }
        else
        {
            $type = null;
        }

        $items = array();
        foreach( Queue::fetchItems( $status, $type ) as $queueItem )
        {
            $item = new stdClass;
            $item->hash = $queueItem->hash;
            $item->title = $queueItem->title;
            $item->progress = $queueItem->progress();
            $items[] = $item;
        }

        $result = new ezcMvcResult;
        $result->variables['status'] = 'ok';
        if ( !count( $items ) )
            $result->variables['message'] = 'no-operation';
        else
            $result->variables['queue'] = $items;
        return $result;
    }

    /**
     * Return the progress for a set of operations
     *
     * @param string $hashes a comma separated list of operations hashes
     * @return ezcMvcResult
     */
    public function doProgress()
    {
        $result = new ezcMvcResult;
    }
}
?>
