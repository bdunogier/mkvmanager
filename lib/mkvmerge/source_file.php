<?php
class MKVMergeSourceFile extends splFileInfo
{
    /**
     * Returns a simple array with 4 keys: path, pathname, basename and size
     */
    public function asArray()
    {
        return array(
            'path'     => $this->getPath(),
            'pathname' => $this->getPathname(),
            'basename' => $this->getBasename(),
            'size'     => $this->getSize(),
        );
    }

    /**
     * Override for the getSize function to support big (>4GB) files
     */
    public function getSize()
    {
        $out = $return_value = false;

        $ret = exec( 'du -bs "' . $this->getPathname() . '"', $out, $return_value );

        if ( $return_value == 0 )
        {
            list( $size, $file ) = explode( "\t", $ret );
            return (double)$size;
        }
        else
        {
            throw new Exception( "Error getting file size: " . print_r( $out, true ) );
        }
    }
}
?>