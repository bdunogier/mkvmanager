<?php
/**
 * Media file analyzer. Will identify the various tracks in a media file.
 *
 * Uses the mkvmerge --identify-verbose and parses the result to do so.
 * Example output:
 * <code>
 * File '/media/storage/STARBUCK/TV Shows/Fringe/Fringe - 1x01 -  Pilot.mkv': container: Matroska [duration:4844384000000]
 * Track ID 1: video (V_MPEG4/ISO/AVC) [language:eng track_name:English display_dimensions:16x9 default_track:1 forced_track:0 packetizer:mpeg4_p10_video]
 * Track ID 2: audio (A_AC3) [language:eng track_name:English default_track:1 forced_track:0]
 * Track ID 3: subtitles (S_TEXT/UTF8) [language:fre track_name:Français default_track:1 forced_track:0]
 * </code>
 */
class MKVMergeMediaAnalyzer
{
    public function __construct( $inputFile )
    {
        if ( !file_exists( $inputFile->file ) )
        {
            throw new ezcBaseFileNotFoundException( $inputFile );
        }
        $this->inputFile = $inputFile;
    }

    /**
     * Analyses the input file and stores the found tracks in $trackSet
     *
     * @throws ezcBaseFileNotFoundException if $mediaFile can't be found
     */
    public function analyze()
    {
        $return = false; $output = false;
        $command = "mkvmerge --identify-verbose \"{$this->inputFile->file}\"";

        exec( $command, $output, $return );

        // @todo: handle this with an exception
        if ( $return != 0 )
        {
            return false;
        }
        else
        {
            $trackSet = new MKVmergeCommandTrackSet();
            preg_match_all( "/^Track ID ([0-9]+): (video|audio|subtitles) \((.+)\)(?: \[language:([a-z]{3}).*\])?$/im", join( "\n", $output ), $matches, PREG_SET_ORDER );
            foreach( $matches as $match )
            {
                $index = $match[1];
                $type = $match[2];

                // language doesn't exist for AVI (nor the other properties)
                $language = isset( $match[4] ) ? $match[4] : false;
                switch( $type )
                {
                    case 'video':
                        $trackSet[$index] = new MKVmergeCommandVideoTrack( $this->inputFile, $index );
                        if ( $language )
                            $trackSet[$index]->language = $language;
                        break;
                    case 'audio':
                        $trackSet[$index] = new MKVmergeCommandAudioTrack( $this->inputFile, $index );
                        if ( $language )
                            $trackSet[$index]->language = $language;
                        break;
                    case 'subtitles':
                        $trackSet[$index] = new MKVmergeCommandSubtitleTrack( $this->inputFile, $index );
                        if ( $language )
                            $trackSet[$index]->language = $language;
                        break;
                    default:
                        throw new Exception( "Unhandled track type $type (" . print_r( $match, true ) . ')' );
                }
            }
            $this->trackSet = $trackSet;
        }
    }

    /**
     * The tracks found in the analyzed media file
     * @var MKVMergeCommandTrackSet
     */
    public $trackSet = null;

    /**
     * @var MKVMergeMediaInputFile
     */
    private $inputFile;
}
?>