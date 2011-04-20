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
        if ( !file_exists( (string)$inputFile ) )
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
    private function analyze()
    {
        $return = false; $output = false;
        $command = "mkvmerge --identify-verbose \"{$this->inputFile}\"";

        exec( $command, $output, $return );

        // @todo: handle this with an exception
        if ( $return != 0 )
        {
            return false;
        }
        else
        {
            $this->analysisResult = array();
            preg_match_all( "/^Track ID ([0-9]+): (video|audio|subtitles) \((.+)\)(?: \[(.*)\])?$/im", join( "\n", $output ), $matches, PREG_SET_ORDER );
            foreach( $matches as $match )
            {
                $index = $match[1];
                $type = $match[2];

                // language doesn't exist for AVI (nor the other properties)
                $this->analysisResult[$index] = new stdClass;
                $this->analysisResult[$index]->index = $index;
                $this->analysisResult[$index]->type = $type;
                // meta informations
                if ( isset( $match[4] ) )
                {
                    foreach( explode( ' ', $match[4] ) as $metaProperty )
                    {
                        list( $name, $value ) = explode( ':', $metaProperty );
                        $this->analysisResult[$index]->$name = $value;
                    }
                }
            }
        }
    }

    /**
     * Analyzes the input file and returns the tracks it contains
     * @return array(index=>array(type,index,language)
     */
    public function getResult()
    {
        if ( $this->analysisResult == null )
        {
            $this->analyze();
        }
        return $this->analysisResult;
    }

    private $analysisResult = array();

    /**
     * @var MKVMergeMediaInputFile
     */
    private $inputFile;
}
?>