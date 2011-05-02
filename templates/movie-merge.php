<h1>Merge movie: <?=$this->movie?></h1>

<h2>Tracks</h2>

<ol>
<? foreach ( $this->tracks as $track ): ?>
    <li><?=displayTrackType($track)?> (<?=basename( (string)$track->inputFile )?>),
        language = <?=$track->language?>,
        default = <?=$track->default_track ? 'yes' :  'no'?>,
        forced = <?=$track->forced_track ? 'yes' :  'no'?>,
        enabled = <?=$track->enabled ? 'yes' :  'no'?>
    </li>
<? endforeach ?>
</ol>

<h2>Command</h2>
<p><?=$this->command?></p>

<?php
function displayTrackType( $trackObject )
{
    $trackTypeMapping = array(
        'MKVmergeCommandVideoTrack' => 'video',
        'MKVMergeCommandAudioTrack' => 'audio',
        'MKVmergeCommandSubtitleTrack' => 'subtitles'
    );

    if ( isset( $trackTypeMapping[get_class( $trackObject )] ) )
        return $trackTypeMapping[get_class( $trackObject )];
    else
        return 'unknown';
}
?>