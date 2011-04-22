<?php
/**
 * A set of MKVmergeCommandTrack
 * @property-read boolean $hasSubtitles
 * @property-write boolean $hasAudio
 * @property-write boolean $hasVideo
 */
class MKVmergeCommandTrackSet implements ArrayAccess, Iterator, Countable
{
    /**
     * Returns the value of $property
     */
    public function __get( $property )
    {
        switch( $property )
        {
            case 'hasSubtitles':
                return $this->hasTrackType( 'subtitles' );
                break;

            case 'hasVideo':
                return $this->hasTrackType( 'video' );
                break;

            case 'hasAudio':
                return $this->hasTrackType( 'audio' );
                break;

            default:
                throw new ezcBasePropertyNotFoundException( $property );
        }
    }

    /**
     * Tests if the set contains the track type $type
     * @param string $type audio, subtitles, video
     * @return boolean
     */
    private function hasTrackType( $type )
    {
        $typeMapping = array(
            'subtitles' => 'MKVmergeCommandSubtitleTrack',
            'audio' => 'MKVmergeCommandAudioTrack',
            'video' => 'MKVmergeCommandVideoTrack'
        );
        if ( !isset( $typeMapping['type'] ) )
            throw new ezcBaseValueException( 'type', $type, implode( ', ', array_values( $typeMapping ) ) );

        foreach( $this->tracks as $track )
        {
            if ( $track instanceof $typeMapping[$type] )
                return true;
            return false;
        }
    }

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