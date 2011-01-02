<?php
return array(
    'MKVMergeCommand'                 => 'mkvmerge/command.php',
    'MKVMergeCommandQueue'            => 'mkvmerge/commandqueue.php',
    'MKVMergeCommandImportWindowsGUI' => 'mkvmerge/commandimportwindowsgui.php',
    'MKVMergeSourceFile'              => 'mkvmerge/source_file.php',
    'MKVMergeTVCommandGenerator'      => 'mkvmerge/command_generator.php',

    'mmMergeOperation' => 'mkvmerge/merge_operation.php',

    'mmApp' => 'app.php',

    'mmMvcConfiguration' => 'mvc/config.php',
    'mmMvcRouter'        => 'mvc/router.php',

    'mmHtmlView'         => 'mvc/views/html.php',
    'mmAjaxView'         => 'mvc/views/ajax.php',

    'mmMkvManagerController' => 'mvc/controllers/mkvmanager.php',
    'mmAjaxController'       => 'mvc/controllers/ajax.php',

    'mmMvcResultStatusNotFound' => 'mvc/result_status/error_not_found.php',
    'mmMvcResultStatusError'    => 'mvc/result_status/error.php',

    'mmMkvManagerDiskHelper' => 'mkvmanager/disk_helper.php',
    'mmMkvManagerSubtitles'  => 'mkvmanager/subtitles.php',

    'UnsortedEpisodesFilter' => 'iterators/unsorted_episodes.php',

    'MkvManagerScraper'           => 'mkvmanager/interfaces/scraper.php',
    'MkvManagerScraperBetaSeries' => 'mkvmanager/scraper_betaseries.php',

    'MkvManagerScraperHTTPException' => 'mkvmanager/exceptions/scraper_http.php',
    'MkvManagerScraperHTMLException' => 'mkvmanager/exceptions/scraper_html.php',

)?>