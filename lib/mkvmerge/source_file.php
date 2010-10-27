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
}
?>