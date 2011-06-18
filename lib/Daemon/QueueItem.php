<?php
namespace mm\Daemon;
use ezcPersistentSessionInstance;
use stdClass;

/**
 * A daemon queue item
 *
 * References a background operation (mm\Daemon\BackgroundOperation) that can be executed
 */
class QueueItem
{
    public $hash = null;
    public $createTime = 0;
    public $startTime = 0;
    public $endTime = 0;
    public $status = null;
    public $message = '';
    public $objectString = null;
    public $object = null;
    public $pid = -1;

    public function __construct( BackgroundOperation $operation = null )
    {
        $this->createTime = time();
        $this->startTime = 0;
        $this->endTime = 0;
        $this->status = self::STATUS_PENDING;
        $this->object = $operation;
        $this->objectString = var_export( $this->object, true );
        $this->hash = sha1( var_export( $this, true ) );
    }

    public function getState()
    {
        $result = array();
        $result['hash'] = $this->hash;
        $result['createTime'] = $this->createTime;
        $result['startTime'] = $this->startTime;
        $result['endTime'] = $this->endTime;
        $result['status'] = $this->status;
        $result['message'] = $this->message;
        $result['objectString'] = $this->objectString;
        return $result;
    }

    public function setState( array $properties )
    {
        foreach( $properties as $key => $value )
        {
            $this->$key = $value;
        }
        $this->object = eval( "return $this->objectString;" );
    }

    /**
     * Fetch a QueueItem based on its hash
     *
     * @param string $hash
     * @return QueueItem
     */
    public static function fetchByHash( $hash )
    {
        $session = ezcPersistentSessionInstance::get();
        return $session->loadIfExists( 'QueueItem', $hash );
    }

    /**
     * Resets the item to pending
     */
    public function reset()
    {
        $this->status = self::STATUS_PENDING;
        $this->startTime = 0;
        $this->endTime = 0;
        $this->message = '';
        $this->store();
    }

    /**
     * Updates the stored version of the process
     */
    public function update()
    {
        ezcPersistentSessionInstance::get()->update( $this );
    }

    public function store()
    {
        ezcPersistentSessionInstance::get()->save( $this );
    }

    public function run()
    {
        $this->object->run();
    }

    /**
     * Returns the objects as a struct
     *
     * @return ezcBaseStruct
     */
    public function asStruct()
    {
        $struct = new stdClass();
        foreach( $this as $property => $value )
        {
            $struct->$property = $value;
        }
        $struct->progress = $this->progress();
        return $struct;
    }

    const STATUS_ARCHIVED = 4;
    const STATUS_PENDING = 3;
    const STATUS_RUNNING = 2;
    const STATUS_ERROR = 1;
    const STATUS_DONE = 0;

}

?>