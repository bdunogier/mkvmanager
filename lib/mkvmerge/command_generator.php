<?php
/**
 * Generates a MKVMergeCommand from a TV Show file
 *
 * Basic example:
 * <code>
 * $generator = new MKVMergeCommandGenerator;
 * $generator->addInputFile( new MKVMergeMediaInputFile( '/path/to/TVShows - 1x01 - Title.avi' ) );
 * $generator->addInputFile( new MKVMergeSubtitleInputFile( '/path/to/TVShows - 1x01 - Title.srt' ) );
 * $generator->setOutputFile( '/storage/TVShows - 1x01 - Title.mkv' );
 * $command = $generator->getCommand();
 * </code>
 */
class MKVMergeCommandGenerator
{
    /**
     * Constructs a new command generator object
     */
    public function __construct()
    {
        $this->tracks = new MKVmergeCommandTrackSet();
    }

    /**
     * Adds the input file $file to the command
     * @param MKVMergeSourceFile $file
     * @return MKVMergeCommandTrackSet The added track set
     */
    public function addInputFile( MKVMergeInputFile $inputFile )
    {
        // local track set we will return at the end
        $trackSet = new MKVMergeCommandTrackSet();

        // media file: analyze and add found tracks
        if ( $inputFile instanceof MKVMergeMediaInputFile )
        {
            $analyzer = $this->getAnalyzer( $inputFile );
            foreach( $analyzer->getResult() as $analysisResult )
            {
                $trackSet[] = MKVMergeCommandTrack::fromAnalysisResult( $analysisResult, $inputFile );
            }
        }
        // subtitle file: add as is
        elseif ( $inputFile instanceof MKVMergeSubtitleInputFile )
        {
            $trackSet[] = new MKVmergeCommandSubtitleTrack( $inputFile );
        }
        $this->inputFiles[] = $inputFile;
        $this->trackSets[] = $trackSet;

        return $trackSet;
    }

    /**
     * Sets the target disk for the output file to $disk
     *
     * This method is a helper for setOutputFile
     *
     * @param string $disk
     */
    public function setTargetDisk( $disk )
    {

    }

    /**
     * Sets the output path for the command to $outputPath
     *
     * @param string $outputPath
     */
    public function setOutputFile( $outputPath )
    {
        $this->outputPath = $outputPath;
    }

    /**
     * Sets the analyzer class to $analyzerClass
     * @param string $analyzerClass
     */
    public function setAnalyzer( $analyzerClass )
    {
        $this->analyzer = $analyzerClass;
    }

    /**
     * Returns the media analyzer as configured using setAnalyzer
     * @return MKVmergeMediaAnalyzer
     */
    private function getAnalyzer( MKvMergeMediaInputFile $inputFile )
    {
        return new $this->analyzer( $inputFile );
    }

    /**
     * Returns the executable part of the command
     * @return string
     */
    private function getCommandPartExecutable()
    {
        return 'mkvmerge';
    }

    /**
     * Returns the output path part of the command
     * @return string -o 'output file'
     */
    private function getCommandPartOutputPath()
    {
        if ( !$this->outputPath )
        {
            throw new Exception( "outputPath has not been set" );
        }
        return sprintf( '-o %s', escapeshellarg( $this->outputPath ) );
    }

    /**
     * Returns the tracks command line parts
     * @return array
     */
    private function getCommandTrackParts()
    {
        $return = array();

        // generate lines for each track
        $currentInputFile = false;

        // iterate track sets
        $inputFileIndex = 0;
        foreach( $this->trackSets as $trackSet )
        {
            $command = '';
            $audioTracksIndexes = array();
            $videoTracksIndexes = array();
            $subtitleTracksIndexes = array();
            foreach( $trackSet as $track )
            {
                if ( !$track->enabled )
                    continue;

                // subtitle track
                if ( $track instanceof MKVmergeCommandSubtitleTrack )
                {
                    $subtitleTracksIndexes[] = $track->index;

                    $command .= "--language {$track->index}:{$track->language} ";
                    $command .= "--sub-charset {$track->index}:{$track->fileCharset} ";

                    if ( $track->forced_track !== null )
                        $command .= "--forced-track {$track->index}:" . ( $track->forced_track ? 'yes' : 'no' ) . ' ';
                    if ( $track->default_track !== null )
                        $command .= "--default-track {$track->index}:" . ( $track->default_track ? 'yes' : 'no' ) . ' ';
                }

                // audio track
                elseif ( $track instanceof MKVMergeCommandVideoTrack )
                {
                    $videoTracksIndexes[] = $track->index;

                    $command .= "--language {$track->index}:{$track->language} ";
                    if ( $track->forced_track !== null )
                        $command .= "--forced-track {$track->index}:" . ( $track->forced_track ? 'yes' : 'no' ) . ' ';
                    if ( $track->default_track !== null )
                        $command .= "--default-track {$track->index}:" . ( $track->default_track ? 'yes' : 'no' ) . ' ';
                }

                // audio track
                elseif ( $track instanceof MKVMergeCommandAudioTrack )
                {
                    $audioTracksIndexes[] = $track->index;

                    $command .= "--language {$track->index}:{$track->language} ";
                    if ( $track->forced_track !== null )
                        $command .= "--forced-track {$track->index}:" . ( $track->forced_track ? 'yes' : 'no' ) . ' ';
                    if ( $track->default_track !== null )
                        $command .= "--default-track {$track->index}:" . ( $track->default_track ? 'yes' : 'no' ) . ' ';
                }
            }

            // "-a n,m" Copy the audio tracks n,m etc
            // "-A" Don't copy any audio track from this file.
            $command .= ( count( $audioTracksIndexes )    ? "-a " . implode( ',', $audioTracksIndexes ) : "-A" ) . " ";

            // "-d n,m" Copy the video tracks n, m etc
            // "-D" Don't copy any video track from this file
            $command .= ( count( $videoTracksIndexes )    ? "-d " . implode( ',', $videoTracksIndexes ) : "-D" ) . " ";

            // "-s n,m" // Copy the subtitle tracks n, m etc.
            // "-S" Don't copy any subtitle track from this file.
            $command .= ( count( $subtitleTracksIndexes )    ? "-s " . implode( ',', $subtitleTracksIndexes ) : "-S" ) . " ";

            $command .=
                "-T " . // Don't copy tags for tracks from the source file.
                "-M " . // Don't copy attachments from this file.
                "--no-global-tags " . // Don't keep global tags from the source file.
                // "--no-chapters " . // Don't keep chapters from the source file <= please do keep chapters !
                escapeshellarg( (string)$this->inputFiles[$inputFileIndex] );

            $return[] = $command;

            $inputFileIndex++;
        }

        // TODO: add track order
        // $template = '"--track-order" "0:0,1:1,1:2"';

        return $return;
    }
    /**
     * Returns the command string
     * @return string
     */
    public function getCommandString()
    {
        $commandParts = array( $this->getCommandPartExecutable(), $this->getCommandPartOutputPath() );
        $commandParts = array_merge( $commandParts, $this->getCommandTrackParts() );

        $command = implode( " ", $commandParts );

        return $command;
    }

    /**
     * Returns the command
     * @return MKVMergeCommand
     */
    public function get()
    {
        return new MKVMergeCommand( $this->getCommandString() );
    }

    /**
     * The tracks the command manages, one for each $inputFile item
     * @var array(MKVMergeCommandGeneratorTrackSet)
     */
    public $trackSets;

    /**
     * The command's input files
     * @var array(MKVMergeInputFile)
     */
    private $inputFiles;

    /**
     * The media analyzer class
     * @var string
     */
    private $analyzer = 'MKVMergeMediaAnalyzer';

    /**
     * The command's outut path
     * @var string
     */
    private $outputPath = false;
}
?>