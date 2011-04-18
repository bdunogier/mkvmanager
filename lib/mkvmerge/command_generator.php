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
        return sprintf( '-o %s', escapeshellarg( $this->outputPath ) );
    }

    /**
     * Returns the command string
     * @return string
     */
    private function getCommandString()
    {
        $commandParts = array(
            $this->getCommandPartExecutable(),
            $this->getCommandPartOutputPath(),
        );

        // generate lines for each track
        foreach( $this->tracks as $track )
        {
        }

        // add track order

        $command = implode( ' ', $commandParts );

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
     * Adds the input file $file to the command
     * @param MKVMergeSourceFile $file
     */
    public function addInputFile( MKVMergeInputFile $inputFile )
    {
        /**
         * @var MKVMergeMediaAnalyzer
         */
        $analyzer = new $this->analyzer( $inputFile );

        foreach( $analyzer->getResult() as $analysisResult )
        {
            $this->addTrack( MKVMergeCommandTrack::fromAnalysisResult( $analysisResult, $inputFile ) );
        }

        $this->inputFiles = $inputFile;

        // Note: we should be able to manipulate the added tracks further on...
        // Use MKVMergeCommandTrackSet ? Is this usable ? Useful ?
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
     * Adds the track $track to the command
     * @param MKVMergeCommandTrack $track
     */
    private function addTrack( MKVMergeCommandTrack $track )
    {
        $this->tracks[] = $track;
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
     * The tracks the command manages
     * @var MKVMergeCommandGeneratorTrackSet
     */
    private $tracks;

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