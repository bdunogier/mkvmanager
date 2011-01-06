<?php
/**
* SQL Table:
* CREATE TABLE commands (
* hash TEXT PRIMARY KEY,
* start_time INTEGER,
* end_time INTEGER,
* command TEXT,
* target_file TEXT,
* target_file_size INTEGER,
* pid INTEGER,
* status INTEGER,
* message TEXT );
*
* @property-read MKVMergeCommand $commandObject
**/
class mmMergeOperation
{
    public $hash = null;
    public $startTime = null;
    public $endTime = null;
    public $command = null;
    public $targetFile = null;
    public $targetFileSize = null;
    public $pid = null;
    public $status = null;
    public $message = null;

    public function getState()
    {
        $result = array();
        $result['hash'] = $this->hash;
        $result['startTime'] = $this->startTime;
        $result['endTime'] = $this->endTime;
        $result['command'] = $this->command;
        $result['targetFile'] = $this->targetFile;
        $result['targetFileSize'] = $this->targetFileSize;
        $result['pid'] = $this->pid;
        $result['status'] = $this->status;
        $result['message'] = $this->message;
        return $result;
    }

    public function setState( array $properties )
    {
        foreach( $properties as $key => $value )
        {
            $this->$key = $value;
        }
    }

    /**
     * Returns the next pending operzation
     * @return mmMergeOperation
     */
    public static function next()
    {
        $session = ezcPersistentSessionInstance::get();

        $query = $session->createFindQuery( 'mmMergeOperation' );
        $query->where( $query->expr->eq( 'status', $query->bindValue( self::STATUS_PENDING ) ) )
              ->limit( 1 );

        $pendingOperations = $session->find( $query );
        if ( count( $pendingOperations ) == 1 )
            return array_pop( $pendingOperations );
        else
            return false;
    }

    public function __get( $property )
    {
        switch( $property )
        {
            case 'commandObject':
                $value = new MKVMergeCommand( $this->command );
                break;

            default:
                throw new ezcBasePropertyNotFoundException( $property );
        }

        return $value;
    }

    /**
     * Fetch a mmMergeOperation based on its hash
     *
     * @param string $hash
     * @return mmMergeOperation
     */
    public static function fetchByHash( $hash )
    {
        $session = ezcPersistentSessionInstance::get();
        return $session->loadIfExists( 'mmMergeOperation', $hash );
    }

    /**
     * Adds a command to the queue
     * @param string $command
     * @return mmMergeOperation
     */
    public static function queue( $command )
    {
        $hash = sha1( $command );
        $session = ezcPersistentSessionInstance::get();

        $commandObject = new MKVMergeCommand( $command );

        $mergeOperation = new mmMergeOperation();
        $mergeOperation->hash = $hash;
        $mergeOperation->startTime = time();
        $mergeOperation->pid = 0;
        $mergeOperation->status = mmMergeOperation::STATUS_PENDING;
        $mergeOperation->command = $command;
        $mergeOperation->targetFile = $commandObject->targetPath;
        $mergeOperation->targetFileSize = $commandObject->TargetSize;

        try {
            $session->save( $mergeOperation );
        } catch( ezcPersistentObjectAlreadyPersistentException  $e ) {
            // @todo Add status check
            throw new mmMergeOperationAlreadyQueued( $command );
        }

        return $mergeOperation;
    }

    /**
     * Returns a merge operation's progress, as a rounded percentage
     *
     * @return int
     */
    public function progress()
    {
        var_dump( $this->targetFile );
        $currentTargetSize = mmMkvManagerDiskHelper::bigFileSize( $this->targetFile );
        return round( $currentTargetSize / $this->targetFileSize * 100 );
    }

    const STATUS_PENDING = 3;
    const STATUS_RUNNING = 2;
    const STATUS_ERROR = 1;
    const STATUS_DONE = 0;
}

class mmMergeOperationAlreadyQueued extends ezcBaseException
{
    public function __construct( $command )
    {
        parent::__construct( "Command already queued: $command" );
    }
}
?>