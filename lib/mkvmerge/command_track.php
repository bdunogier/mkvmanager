<?php
/**
 * An MKVMerge command track
 *
 * @property MKVMergeInputFile $inputFile The file the track is taken from
 * @property integer $track The file's index in the input file, starting from 0
 * @property string $language The track's language, as a 3 letters code
 * @property string $default_track
 * @property string $forced_track
 * @property string $enabled
 */
class MKVmergeCommandTrack
{
    /**
     * Constructs a new MKVMergeCommandTrack from track #$index of file $inputFile
     *
     * @param MKVMergeInputFile $inputFile
     * @param mixed $index
     */
    public function __construct( MKVMergeInputFile $inputFile, $index = 0 )
    {
        $this->inputFile = $inputFile;
        $this->index = $index;
    }

    public function __set( $property, $value )
    {
        if ( !array_key_exists( $property, $this->properties ) )
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
            case 'index':
                if ( !is_numeric( $value ) )
                    throw new ezcBaseValueException( 'index', $value, 'integer' );
                $value = (int)$value;
                break;
            case 'language':
                if ( strlen( $value ) != 3 )
                    throw new ezcBaseValueException( 'language', $value, 'three letters language code' );
                break;
            case 'default_track':
            case 'forced_track':
                $value = (bool)$value;
                break;

        }
        $this->properties[$property] = $value;
    }

    public function __get( $property )
    {
        if ( !array_key_exists( $property, $this->properties ) )
        {
            throw new ezcBasePropertyNotFoundException( $property );
        }
        else
        {
            if ( $property == 'language' && $this->properties[$property] === false )
                return 'und';
            else
                return $this->properties[$property];
        }
    }

    public function __isset( $property )
    {
        return isset( $this->properties[$property] ) && ( $this->properties[$property] !== false );
    }

    /**
     * Returns the appropriate MKVMergeCommandTrack from the MKVMergeMediaAnalyzer $analysisResult and input file $inputFile
     * @param stdClass $analysisResult
     * @param MKVMergeInputFile $inputFile
     * @return MKVMergeCommandTrack
     */
    public static function fromAnalysisResult( stdClass $analysisResult, MKVMergeInputFile $inputFile )
    {
        switch( $analysisResult->type )
        {
            case 'audio':
                $track = new MKVMergeCommandAudioTrack( $inputFile, $analysisResult->index );
                break;
            case 'video':
                $track = new MKVMergeCommandVideoTrack( $inputFile, $analysisResult->index );
                break;
            case 'subtitles':
                $track = new MKVmergeCommandSubtitleTrack( $inputFile, $analysisResult->index );
                break;
            default:
                throw new Exception( "Unhandled track type $track" );
        }
        foreach( $analysisResult as $property => $value )
        {
            try {
                $track->$property = $value;
            } catch ( ezcBasePropertyNotFoundException $e ) {}
        }

        return $track;
    }

    /**
     * Returns the string representation (the input file path)
     */
    public function fromString()
    {
        return (string)$this->inputFile->file;
    }

    protected $properties = array(
        'inputFile' => false,
        'index' => false,
        'language' => false,
        'default_track' => null,
        'forced_track' => null,
        'enabled' => true,
    );
}
?>
