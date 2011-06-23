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
        if ( !isset( $this->type ) )
        {
            switch( $type )
            {
                case 'archived': $type = QueueItem::STATUS_ARCHIVED; break;
                case 'done':     $type = QueueItem::STATUS_DONE; break;
                case 'error':    $type = QueueItem::STATUS_ERROR; break;
                case 'pending':  $type = QueueItem::STATUS_PENDING; break;
                case 'running':  $type = QueueItem::STATUS_RUNNING; break;
            }
        }
        else
        {
            $type = null;
        }

        $items = array();
        foreach( Queue::fetchItems( $this->status, $type ) as $queueItem )
        {
            $item = new stdClass;
            $item->hash = $queueItem->hash;
            $item->title = $queueItem->title;
            $item->progress = $queueItem->progress();
            $items[] = $item;
        }

        $result = new ezcMvcResult;
        $result->variables['status'] = 'ok';
        $result->variables['queue'] = $items;
        return $result;
    }
}
?>
