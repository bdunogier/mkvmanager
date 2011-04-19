<?php
/**
 * MKVMergeSubtitleInputFile
 * One subtitle file, with its language
 */
class MKVMergeSubtitleInputFile extends MKVMergeInputFile
{
    function __construct( $file, $language )
    {
        parent::__construct( $file );
        $this->language = $language;
    }

    private $language;
}
?>