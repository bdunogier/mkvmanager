<?php
/**
 * A generic input file for an MKVMerge command
 */
abstract class MKVMergeInputFile
{
    function __construct( $file )
    {
        $this->file = $file;
    }

    /**
     * Returns the input file's tracks
     * @return MKVmergeCommandTrackSet
     */
    public function getTracks()
    {
        return $tracks;
    }

    public function __toString()
    {
        return $this->file;
    }

    /**
     * @var string
     */
    protected $file;
}
?>