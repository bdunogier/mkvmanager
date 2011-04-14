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
    public function __construct( MKVMergeInputFile $inputFile, $index = 0 )
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
                $track = new MKVMergeCommandSubtitleTrack( $inputFile, $analysisResult->index );
                break;
            default:
                throw new Exception( "Unhandled track type $track" );
        }
        if ( isset( $analysisResult->language ) )
            $track->language = $analysisResult->language;

        return $track;
    }

    /**
     * Returns the string representation (the input file path)
     */
    public function fromString()
    {
        return (string)$this->inputFile->file;
    }

    private $properties = array(
        'inputFile' => false,
        'trackIndex' => false,
        'language' => false,
    );
}
?>