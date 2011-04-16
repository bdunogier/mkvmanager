<?php
/**
 * MKVMergeInputFile
 * Will analyze the file for tracks
 */
class MKVMergeMediaInputFile extends MKVMergeInputFile
{
    function __construct( $file )
    {
        $this->file = $file;

        /**
         * @var MKVMergeMediaAnalyzer
         */
        $analyzer = new self::$analyzer( $this );
        $analyzer->analyze();
        $this->tracks = clone $analyzer->trackSet;
    }

    /**
     * Sets the video analyzing class to $analyzerClass
     * @param string $analyzerClass
     */
    public static function setAnalyzer( $analyzerClass )
    {
        // @todo Add ReflectionClass check on the interface
        self::$analyzer = $analyzerClass;
    }

    /**
     * @var string
     */
    public $file;


    /**
     * The input file tracks
     * @var MKVMergeCommandTrackSet
     */
    private $tracks;

    /**
     * The media analyzer class
     * @var string
     */
    private static $analyzer = 'MKVMergeMediaAnalyzer';
}
?>