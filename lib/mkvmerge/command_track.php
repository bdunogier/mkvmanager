<?php
/**
 * An MKVMerge command track
 *
 * @property MKVMergeInputFile $inputFile The file the track is taken from
 * @property integer $track The file's index in the input file, starting from 0
 * @property string $language The track's language, as a 3 letters code
 */
class MKVmergeCommandTrack
{
    /**
     * Constructs a new MKVMergeCommandTrack from track #$index of file $inputFile
     *
     * @param MKVMergeInputFile $inputFile
     * @param mixed $index
     */
    public function __construct( MKVMergeInputFile $inputFile, $index )
    {
        $this->inputFile = $inputFile;
        $this->trackIndex = $index;
    }

    public function __set( $property, $value )
    {
        if ( !isset( $this->properties[$property] ) )
        {
            throw new ezcBasePropertyNotFoundException( $property );
        }

        // value check / preprocessing
        switch( $property )
        {
            case 'inputFile':
                if ( !$value instanceof MKVMergeInputFile )
                    throw new ezcBaseValueException( 'inputFile', $value, 'instanceof MKVMergeInputFile' );
                break;
            case 'trackIndex':
                if ( !is_numeric( $value ) )
                    throw new ezcBaseValueException( 'trackIndex', $value, 'integer' );
                $value = (int)$value;
                break;
            case 'language':
                if ( strlen( $value ) != 3 )
                    throw new ezcBaseValueException( 'language', $value, 'three letters language code' );
                break;
        }
        $this->properties[$property] = $value;
    }

    public function __get( $property )
    {
        if ( !isset( $this->properties[$property] ) )
        {
            throw new ezcBasePropertyNotFoundException( $property );
        }
        else
        {
            return $this->properties[$property];
        }
    }

    public function __isset( $property )
    {
        return isset( $this->properties[$property] ) && ( $this->properties[$property] !== false );
    }

    private $properties = array(
        'inputFile' => false,
        'trackIndex' => false,
        'language' => false,
    );
}
?>