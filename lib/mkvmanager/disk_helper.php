<?php
class mmMkvManagerDiskHelper
{
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

    /**
     * Returns the best fit, depending on name, chronology and size, for a new
     * TV episode.
     *
     * @param string $episodeName The full episode name: ShowName - SSEXX - Title
     * @param string $freeSpaceTreshold minimal available space for a disk  to be available
     *
     * @todo Make the treshold depend on the episode Index, maybe even based on previous seasons and episode size
     *
     * @return array string a disk name (HDEXT-1, ARTHAS...)
     **/
    public static function BestTVEpisodeFit( $episodeName, $targetFilesize )
    {
        $return = array();
        $basePath = '/media/aggregateshares/TV Shows';

        $targetEpisodeInfo = self::parseEpisode( $episodeName );
        $showAggregatePath = "{$basePath}/{$targetEpisodeInfo['show']}";

        if ( !file_exists( $showAggregatePath ) )
        {
            $return['bestfit'] = 'none';
        }
        else
        {
            $iterator = new FilesystemIterator( $showAggregatePath );
            $iterator->setFlags( FilesystemIterator::KEY_AS_FILENAME | FilesystemIterator::CURRENT_AS_PATHNAME );
            $maxEpisodeNumber = 0;
            foreach( $iterator as $file => $path )
            {
                $episodeInfo = self::parseEpisode( $file );
                if ( $episodeInfo === false )
                    continue;

                $path = realpath( $path );
                // 100 episodes per season really should do it, right ?
                $absoluteEpisodeNumber = ( $episodeInfo['season'] * 100 ) + $episodeInfo['episode'];
                if ( $absoluteEpisodeNumber > $maxEpisodeNumber )
                {
                    $maxEpisodeNumber = $absoluteEpisodeNumber;
                    $latestEpisodePath = $path;
                }
            }
            if ( !isset( $latestEpisodePath ) )
            {
                $return['bestfit'] = 'none';
            }
            else
            {
                $return['LatestEpisode'] = $latestEpisodePath;
                list( , , , $return['RecommendedDisk'] ) = explode( '/', $latestEpisodePath );
                $lastEpisodeDiskFreespace = diskfreespace( $latestEpisodePath );

                $return['RecommendedDiskHasFreeSpace'] = ( $targetFilesize  > $lastEpisodeDiskFreespace ) ? 'false' : 'true';
            }
        }

        return $return;
    }

    /**
     * Parses an episode filename, with of without extension
     *
     * @todo Add an mmTVShowEpisode class with a parseEpisode static method
     *
     * @param string $episode Episode title. Format: <show> - <season>x<episode> - <name>[.<extension>]
     * @return array An array with these keys: show, season, episode, name, extension (optional)
     */
    public static function parseEpisode( $episode )
    {
        if ( !preg_match( '/([^\-]+) \- ([0-9]+)x([0-9\-]+) \- (.*?)(?:\.(avi|mkv))?/', $episode, $matches ) )
        {
            return false;
        }
        else
        {
            return array(
                'show'      => trim( $matches[1] ),
                'season'    => (int)$matches[2],
                'episode'   => (int)$matches[3],
                'name'      => trim( $matches[4] ),
                'extension' => isset( $matches[5] ) ? $matches[5] : null
            );
        }
    }

    /**
     * Returns the size of a big file. Only works on unix.
     */
    public static function bigFileSize( $file )
    {
        if ( !file_exists( $file ) )
            throw new ezcBaseFileNotFoundException( $file );
        $ret = exec( "du -bs \"$file\"" );
        list( $size, $file ) = explode( "\t", $ret );
        return (double)$size;
    }

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
            if ( !$rawFreeSpace = @diskfreespace( $disk->getPathname() ) )
                continue;

            $diskName = $disk->getFilename();
            $selectedText = ( $diskName == $target ) ? ' selected="selected"' : '';

            $disk = new stdClass();
            $disk->name = $diskName;
            $disk->rawFreeSpace = $rawFreeSpace;
            $disk->freespace = self::decodeSize( $disk->rawFreeSpace );
            $disk->selectedText = $selectedText;

            // @todo This is bullcrap: if two disks have the same freespace, only the last one will be returned
            // Use a user defined sort method
            $return[] = $disk;
        }

        usort( $return, function( $a, $b ) {
            if ( $a->rawFreeSpace == $b->rawFreeSpace )
                return 0;
            else
                return ( $a->rawFreeSpace < $b->rawFreeSpace ? -1 : 1 );
        });

        return $return;
    }
}
?>