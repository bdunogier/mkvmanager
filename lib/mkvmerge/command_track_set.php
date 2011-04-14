<?php
/**
 * A set of MKVmergeCommandTrack
 */
class MKVmergeCommandTrackSet implements ArrayAccess, Iterator, Countable
{
    /**
     * ArrayAccess::offsetExists()
     */
    public function offsetExists( $offset )
    {
        return isset( $this->tracks[$offset] );
    }

    /**
     * ArrayAccess::offsetGet()
     * @return MKVMergeCommandTrack
     */
    public function offsetGet( $offset )
    {
        return isset( $this->tracks[$offset] ) ? $this->tracks[$offset] : null;
    }

    /**
     * ArrayAccess::offsetSet()
     */
    public function offsetSet( $offset, $value )
    {
        if ( !$value instanceof MKVmergeCommandTrack )
        {
            throw new ezcBaseValueException( "value", $value, 'MKVmergeCommandTrack' );
        }
        if ( $offset === null )
        {
            $this->tracks[] = $value;
        }
        else
        {
            $this->tracks[$offset] = $value;
        }
    }

    /**
     * ArrayAcces::offsetUnset()
     */
    public function offsetUnset( $offset )
    {
        unset( $this->tracks[$offset] );
    }

    /**
     * Iterator::current()
     */
    public function current()
    {
        return $this->tracks[$this->key];
    }

    /**
     * Iterator::key()
     */
    public function key()
    {
        return $this->key;
    }

    /**
     * Iterator::next()
     */
    public function next()
    {
        $this->key++;
    }

    /**
     * Iterator::rewind()
     */
    public function rewind()
    {
        $this->key = 0;
    }

    /**
     * Iterator::valid()
     */
    public function valid()
    {
        return isset( $this->tracks[$this->key] );
    }

    /**
     * Countable::count()
     */
    public function count()
    {
        return count( $this->tracks );
    }

    /**
     * @var array(MKVManagerCommandTrack)
     */
    private $tracks = array();

    /**
     * Iterator key
     * @var integer
     */
    private $key = 0;
}
?>