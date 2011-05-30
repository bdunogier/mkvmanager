<?php
class mmMvcRouter extends ezcMvcRouter
{
    public function createRoutes()
    {
        return array(
            new ezcMvcRailsRoute( '/',          'mmMkvManagerController', 'mkvMerge' ),
            new ezcMvcRailsRoute( '/test',      'mmMkvManagerController', 'test' ),
            new ezcMvcRailsRoute( '/fatal',     'mmMkvManagerController', 'fatal' ),
            new ezcMvcRailsRoute( '/default',   'mmMkvManagerController', 'default' ),
            new ezcMvcRailsRoute( '/default',   'mmMkvManagerController', 'default' ),

            // actual features
            new ezcMvcRailsRoute( '/mkvmerge',             'mmMkvManagerController', 'mkvMerge' ),
            new ezcMvcRailsRoute( '/subtitles',            'mmMkvManagerController', 'subtitles' ),
            new ezcMvcRailsRoute( '/tvshow/image/:image',  'mmMkvManagerController', 'TVShowImage' ),
            new ezcMvcRailsRoute( '/movies-without-nfo',   'mmMkvManagerController', 'MoviesWithoutNFO' ),
            new ezcMvcRailsRoute( '/merge-queue/:items',   'mmMkvManagerController', 'mergeQueue' ),
            new ezcMvcRailsRoute( '/tvdashboard',          'mmMkvManagerController', 'TVDashboard' ),
            new ezcMvcRailsRoute( '/movies',               'mmMkvManagerController', 'Movies' ),

            new ezcMvcRailsRoute( '/nfo/movie/search/:folder',                       'mm\Mvc\Controllers\Movie', 'NfoSearch' ),
            new ezcMvcRailsRoute( '/nfo/movie/generate/:folder/:AllocineId/:TMDbId', 'mm\Mvc\Controllers\Movie', 'NfoGenerate' ),
            new ezcMvcRailsRoute( '/nfo/movie/save/:folder',                         'mm\Mvc\Controllers\Movie', 'NfoSave' ),
            new ezcMvcRailsRoute( '/nfo/movie/update-info',                          'mm\Mvc\Controllers\Movie', 'NfoUpdateInfo' ),

            new ezcMvcRailsRoute( '/movie-merge/:Folder',  'mmMkvManagerController', 'movieMerge' ),

            // AJAX callbacks
            new ezcMvcRailsRoute( '/ajax/fatal',                               'mmAjaxController', 'fatal' ),
            new ezcMvcRailsRoute( '/ajax/mkvmerge',                            'mmAjaxController', 'mkvMerge' ),
            new ezcMvcRailsRoute( '/ajax/bestfit',                             'mmAjaxController', 'bestFit' ),
            new ezcMvcRailsRoute( '/ajax/searchsubtitles/:VideoFile',          'mmAjaxController', 'searchSubtitles' ),
            new ezcMvcRailsRoute( '/ajax/searchsubtitles/:VideoFile/:release', 'mmAjaxController', 'searchSubtitles' ),
            new ezcMvcRailsRoute( '/ajax/merge-status/:mergeHash',             'mmAjaxController', 'mergeStatus' ),
            new ezcMvcRailsRoute( '/ajax/merge-active-status',                 'mmAjaxController', 'mergeActiveStatus' ),
            new ezcMvcRailsRoute( '/ajax/queue-command',                       'mmAjaxController', 'queueCommand' ),
            new ezcMvcRailsRoute( '/ajax/merge-queue/:items',                  'mmAjaxController', 'mergeQueue' ),
            new ezcMvcRailsRoute( '/ajax/sourcefiles/archive/:hash',           'mmAjaxController', 'sourcefileArchive' ),

            new ezcMvcRailsRoute( '/ajax/generate-command/:VideoFile',         'mmAjaxController', 'generateCommand' ),

            new ezcMvcRailsRoute( '/ajax/movie-search/:Release',                       'mmAjaxController', 'movieSearch' ),
            new ezcMvcRailsRoute( '/ajax/movie-search-releases/:MovieId',              'mmAjaxController', 'movieSearchReleases' ),
            new ezcMvcRailsRoute( '/ajax/movie-search-subtitles/:ReleaseId',           'mmAjaxController', 'movieSearchReleaseSubtitles' ),
            new ezcMvcRailsRoute( '/ajax/movie-download-subtitle/:Folder/:SubtitleId', 'mmAjaxController', 'movieDownloadSubtitle' ),

            new ezcMvcRailsRoute( '/ajax/downloadsubtitle/:VideoFile/:DownloadUrl/:Zip/:SubFileName',
                'mmAjaxController', 'downloadSubtitles' ),
            new ezcMvcRailsRoute( '/ajax/downloadsubtitle/:VideoFile/:DownloadUrl/:SubFileName',
                'mmAjaxController', 'downloadSubtitles' ),
            new ezcMvcRailsRoute( '/ajax/generatemergecommand/:VideoFile',
                'mmAjaxController', 'generateMergeCommand' ),
        );
    }
}
?>
