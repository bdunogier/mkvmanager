<?php
/**
 * A generic input file for an MKVMerge command
 */
abstract class MKVMergeInputFile
{
    /**
     * Returns the input file's tracks
     * @return MKVmergeCommandTrackSet
     */
    public function getTracks()
    {
        return $tracks;
    }

    /**
     * Adds a new track to the input file
     */
    protected function addTrack( MKVMergeCommandTrack $track )
    {
        $this->tracks[] = $track;
    }

    /**
     * @var MKVMergeCommandTrackSet
     */
    private $tracks;
}
?>