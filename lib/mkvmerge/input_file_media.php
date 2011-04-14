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
}
?>