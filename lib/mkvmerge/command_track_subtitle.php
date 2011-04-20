<?php
/**
 * An MKVMerge command subtitle track
 * @property-read string $fileCharset
 */
class MKVmergeCommandSubtitleTrack extends MKVMergeCommandTrack
{
    public function __construct( MKVMergeInputFile $inputFile, $index = 0 )
    {
        $this->properties['fileCharset'] = false;
        parent::__construct( $inputFile, $index );
    }

    public function __get( $property )
    {
        if ( $property == 'fileCharset' )
        {
            $finfo = new finfo( FILEINFO_MIME_ENCODING );
            $encoding = strtoupper( $finfo->file( (string)$this->inputFile ) );
            // assume iso by default
            if ( $encoding == 'UNKNOWN-8BIT' )
                $encoding = "ISO-8859-1";
            elseif ( $encoding == 'BINARY' )
                $encoding = "UTF-8";
            return $encoding;
        }
        else
        {
            return parent::__get( $property );
        }
    }
}
?>