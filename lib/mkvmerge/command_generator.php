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
     * Generates an MKVMerge command from a TVShow episode filename $filename
     * @param string $filename
     * @return MKVMergeCommand
     */
    public static function generate( $filename )
    {
        $commandTemplate =
            // OutputFile
            'mkvmerge -o "%s" ' .

            // SourceSubtitleFile
            '"--sub-charset" "0:ISO-8859-1" "--language" "0:fre" "--forced-track" "0:no" "-s" "0" "-D" "-A" "-T" ' .
            '"--no-global-tags" "--no-chapters" "%s" ' .

            // SourceVideoFile
            // Track1 (video)
            '"--language" "1:eng" "--default-track" "1:no" "--forced-track" "1:no" "--display-dimensions" "1:16x9" ' .
            // Track2 (audio)
            '"--language" "2:eng" "--default-track" "2:yes" "--forced-track" "2:no" "-a" "2" "-d" "1" "-S" "-T" ' .
            // Valid for both tracks ?
            '"--no-global-tags" "--no-chapters" "%s" ' .
            '"--track-order" "0:0,1:1,1:2"';

        // input is a .mkv or .avi file
        preg_match( '/^((.*) - [0-9]+x[0-9]+ - (.*))\.(avi|mkv)$/', $filename, $matches );
        $episodeName = $matches[1];
        $showName = $matches[2];
        $directory = "/home/download/downloads/complete/TV/Sorted/{$showName}";
        $videoSourceFile = "{$directory}/{$filename}";

        // 3. replace source video / sub and output file in command
        //
        // Options:
        // - Check subtitle charset
        // - Handle multiple video files (.en.srt + .fr.srt)

        // 1. look for subtitle file, .ass or .srt
        foreach( array( "{$directory}/{$episodeName}.srt", "{$directory}/{$episodeName}.ass" ) as $subtitleFile )
        {
            if ( !file_exists( $subtitleFile ) )
                unset( $subtitleFile );
            else
                break;
        }
        if ( !isset( $subtitleFile ) )
            throw new Exception( "No subtitles found" );

        // 2. extract relevant output path
        //    *** might require a target path choice, like mkvmerge2
        $outputFile = "/media/storage/foobar/TV Shows/{$showName}/{$episodeName}.mkv";
        $command = sprintf( $commandTemplate, $outputFile, $subtitleFile, $videoSourceFile );
        return new MKVMergeCommand( $command );
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
            foreach( $trackSet as $track )
            {
                // subtitle track
                if ( $track instanceof MKVmergeCommandSubtitleTrack )
                {
                    // @todo FIXME
                    $charset = 'ISO-8859-1';
                    $command .= "--sub-charset {$track->index}:{$charset} --language {$track->index}:{$track->language} ";

                    if ( $track->forced_track !== null)
                        $command .= "--forced-track {$track->index}:" . ( $track->forced_track ? 'yes' : 'no' ) . ' ';
                    if ( $track->default_track !== null)
                        $command .= "--default-track {$track->index}:" . ( $track->default_track ? 'yes' : 'no' ) . ' ';
                    $command .= "-s {$track->index} "; // Copy subtitle tracks n,m etc. Default: copy all subtitle tracks.
                    //"-D " . // Don't copy any video track from this file.
                    //"-A " . // Don't copy any audio track from this file.
                    //"-T " // Don't copy tags for tracks from the source file.
                }

                // audio track
                if ( $track instanceof MKVMergeCommandAudioTrack or $track instanceof MKVMergeCommandVideoTrack )
                {
                    if ( $track instanceof MKVMergeCommandVideoTrack )
                    {
                        $command .= "--language {$track->index}:{$track->language} ";
                        if ( $track->forced_track !== null)
                            $command .= "--forced-track {$track->index}:" . ( $track->forced_track ? 'yes' : 'no' ) . ' ';
                        if ( $track->default_track !== null)
                            $command .= "--default-track {$track->index}:" . ( $track->default_track ? 'yes' : 'no' ) . ' ';
                    }
                    elseif ( $track instanceof MKVMergeCommandAudioTrack )
                    {
                        $command .= "--language {$track->index}:{$track->language} ";
                        if ( $track->forced_track !== null)
                            $command .= "--forced-track {$track->index}:" . ( $track->forced_track ? 'yes' : 'no' ) . ' ';
                        if ( $track->default_track !== null)
                            $command .= "--default-track {$track->index}:" . ( $track->default_track ? 'yes' : 'no' ) . ' ';
                    }
                }
            }

            // TODO : set of above track set !
            $command .=
                // "-a $audioTrackIndex " . // Copy the audio tracks n, m etc. The numbers are track IDs which can be obtained with the --identify switch. They're not simply the track numbers (see section TRACK IDS). Default: copy all audio tracks.
                // "-S " . // Don't copy any subtitle track from this file.
                // "-d $videoTrackIndex " . // Copy the video tracks n, m etc. The numbers are track IDs which can be obtained with the --identify switch (see section TRACK IDS). They're not simply the track numbers. Default: copy all video tracks.
                "-T " . // Don't copy tags for tracks from the source file.
                "--no-global-tags " . // Don't keep global tags from the source file.
                "--no-chapters " . // Don't keep chapters from the source file.
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