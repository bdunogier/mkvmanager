<?php
$autoload = array(
    'MKVMergeCommand'                 => 'mkvmerge/command.php',
    'MKVMergeCommandQueue'            => 'mkvmerge/commandqueue.php',
    'MKVMergeCommandImportWindowsGUI' => 'mkvmerge/commandimportwindowsgui.php',

    'MKVMergeSourceFile'              => 'mkvmerge/source_file.php',
    'MKVMergeCommandGenerator'        => 'mkvmerge/command_generator.php',
    'MKVMergeInputFile'               => 'mkvmerge/input_file.php',
    'MKVMergeMediaInputFile'          => 'mkvmerge/input_file_media.php',
    'MKVMergeSubtitleInputFile'       => 'mkvmerge/input_file_subtitle.php',
    'MKVmergeCommandTrackSet'         => 'mkvmerge/command_track_set.php',
    'MKVMergeCommandTrack'            => 'mkvmerge/command_track.php',
    'MKVMergeCommandAudioTrack'       => 'mkvmerge/command_track_audio.php',
    'MKVmergeCommandSubtitleTrack'    => 'mkvmerge/command_track_subtitle.php',
    'MKVMergeCommandVideoTrack'       => 'mkvmerge/command_track_video.php',
    'MKVMergeMediaAnalyzer'           => 'mkvmerge/media_analyzer.php',

    'mmMergeOperation' => 'mkvmerge/merge_operation.php',

    'mmApp' => 'app.php',

    'mmMvcConfiguration' => 'mvc/config.php',
    'mmMvcRouter'        => 'mvc/router.php',

    'mmHtmlView'         => 'mvc/views/html.php',
    'mmAjaxView'         => 'mvc/views/ajax.php',

    'mmMkvManagerController'   => 'mvc/controllers/mkvmanager.php',
    'mmAjaxController'         => 'mvc/controllers/ajax.php',
    'mm\Mvc\Controllers\Movie' => 'mvc/controllers/movie.php',

    'mmMvcResultStatusNotFound' => 'mvc/result_status/error_not_found.php',
    'mmMvcResultStatusError'    => 'mvc/result_status/error.php',

    'mmMkvManagerDiskHelper' => 'mkvmanager/disk_helper.php',
    'mmMkvManagerSubtitles'  => 'mkvmanager/subtitles.php',

    'UnsortedEpisodesFilter' => 'iterators/unsorted_episodes.php',

    'MkvManagerScraper'             => 'mkvmanager/interfaces/scraper.php',
    'MkvManagerScraperBetaSeries'   => 'mkvmanager/scraper_betaseries.php',
    'MkvManagerScraperSoustitreseu' => 'mkvmanager/scraper_soustitreseu.php',
    'MkvManagerScraperSubsynchro'   => 'mkvmanager/scraper_subsynchro.php',
    'MkvManagerScraperAllocine'     => 'mkvmanager/scraper_allocine.php',
    '\MkvManagerScraperAllocine'     => 'mkvmanager/scraper_allocine.php',
    'MkvManagerScraperTMDB'         => 'mkvmanager/scraper_tmdb.php',
    '\MkvManagerScraperTMDB'         => 'mkvmanager/scraper_tmdb.php',

    'MkvManagerScraperHTTPException' => 'mkvmanager/exceptions/scraper_http.php',
    'MkvManagerScraperHTMLException' => 'mkvmanager/exceptions/scraper_html.php',

    'TVEpisodeFile' => 'mkvmanager/tv_episode_file.php',
    'TVEpisodeDownloadedFile' => 'mkvmanager/tv_episode_downloaded_file.php',
    'TVShow' => 'mkvmanager/tv_show.php',
    'TVShowFolder' => 'mkvmanager/tv_show_folder.php',

    'mm\Info\Movie\SearchResult' => 'Info/Movie/SearchResult.php',
    'mm\Info\Movie\Details' => 'Info/Movie/Details.php',
    'mm\Info\Person' => 'Info/Person.php',
    'mm\Info\Actor' => 'Info/Actor.php',
    'mm\Info\Director' => 'Info/Director.php',
    'mm\Info\Trailer' => 'Info/Trailer.php',
    'mm\Info\Image' => 'Info/Image.php',

    'mm\Xbmc\Nfo\Writers\Movie' => 'Xbmc/Nfo/Writers/Movie.php',

    'mm\Daemon\Daemon' => 'Daemon/Daemon.php',
    'mm\Daemon\BackgroundOperation' => 'Daemon/BackgroundOperation.php',
);

return $autoload;
?>
