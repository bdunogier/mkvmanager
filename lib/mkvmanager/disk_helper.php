<?php
class mmMkvManagerDiskHelper
{
    /**
     * Return the list of available storage drives, including their free space
     * @return array( stdClass ) properties: name, freespace, selectedText
     *
     * @todo Handle seletedText differently
     * @todo Move somewhere else
     */
    public static function diskList()
    {
        $dir = opendir( '/media/storage/' );
        foreach( new DirectoryIterator( '/media/storage/' ) as $disk )
        {
            if ( $disk->isDot() )
                continue;

            $target = isset( $_POST['Target'] ) ? $_POST['Target'] : false;
            $rawFreeSpace = diskfreespace( $disk->getPathname() );

            $diskName = $disk->getFilename();
            $selectedText = ( $diskName == $target ) ? ' selected="selected"' : '';

            $disk = new stdClass();
            $disk->name = $diskName;
            $disk->freespace = $freespace = self::decodeSize( $rawFreeSpace );
            $disk->selectedText = $selectedText;

            // @todo This is bullcrap: if two disks have the same freespace, only the last one will be returned
            // Use a user defined sort method
            $return[] = $disk;
        }
        usort( $return, function( $a, $b ) {
            if ( $a->freespace == $b->freespace )
                return 0;
            else
                return ( $a->freespace < $b->freespace ? -1 : 1 );
        });

        return $return;
    }

    /**
     * Transforms a numnber of bytes in a readable file size
     * @param int $bytes
     * @return string
     *
     * @todo Move somewhere else
     */
    public static function decodeSize( $bytes )
    {
        $types = array( 'B', 'KO', 'MO', 'GO', 'TO' );
        for( $i = 0; $bytes >= 1024 && $i < ( count( $types ) -1 ); $bytes /= 1024, $i++ );
        return round( $bytes, 2 ) . " " . $types[$i];
    }
}
?>