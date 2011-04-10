=======================
Merge command generator
=======================

Goal
====

Easily generate an mkvmerge command based on a set of input files and simple informations.

Stubs
=====

Generate a tv episode mkvmerge command with one subtitle file::

    <?php
    $generator = new MKVMergeCommandGenerator();
    $generator->setOutputFile( '/path/to/output_file.mkv' );

    // @var MKVmergeCommandTrack => won't work, there will be several
    // Each track in the generator will reference the source file.
    // One source file might be referenced by multiple tracks
    $mediaTracks = $generator->addInputFile( new MKVMergeMediaFile( '/path/to/video.mkv' ) );

    // @var MKVmergeCommandTrack
    $subtitleTrack = $generator->addInputFile( new MKVMergeSubtitleFile( '/path/to/video.srt', 'french' ) );

    // @var MKVMergeCommand
    $command = $generator->getCommand();
    ?>

Classes
=======

MKVMergeCommandGenerator
------------------------

The main generator class

MKVMergeTVCommandGenerator
--------------------------

Subclass of MKVMergeCommandGenerator specialized for TV Shows episodes.

MKVMergeMovieCommandGenerator
-----------------------------

Subclass of MKVMergeCommandGenerator specialized for movies

MKVMergeInputFile
-----------------

Abstract class for input files.

MKVMergeSubtitleInputFile
-----------------

Subtitle input files.

MKVMergeInputFile
-----------------

Media input files.

MKVmergeCommandTrack
--------------------

A track in the generator. Each track matches an MKVMergeInputFile. An MKVMergeInputFile matches several tracks.


Challenges
==========

Makes track management easy
---------------------------

- It should be possible to easily identify media tracks after addition
- It should be easy to move these tracks up/down/to top/to bottom
