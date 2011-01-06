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
            new ezcMvcRailsRoute( '/mkvmerge',            'mmMkvManagerController', 'mkvMerge' ),
            new ezcMvcRailsRoute( '/subtitles',           'mmMkvManagerController', 'subtitles' ),
            new ezcMvcRailsRoute( '/tvshow/image/:image', 'mmMkvManagerController', 'TVShowImage' ),
            new ezcMvcRailsRoute( '/movies-without-nfo',  'mmMkvManagerController', 'MoviesWithoutNFO' ),
            new ezcMvcRailsRoute( '/merge-queue',         'mmMkvManagerController', 'mergeQueue' ),

            // AJAX callbacks
            new ezcMvcRailsRoute( '/ajax/fatal',                               'mmAjaxController', 'fatal' ),
            new ezcMvcRailsRoute( '/ajax/mkvmerge',                            'mmAjaxController', 'mkvMerge' ),
            new ezcMvcRailsRoute( '/ajax/bestfit',                             'mmAjaxController', 'bestFit' ),
            new ezcMvcRailsRoute( '/ajax/searchsubtitles/:VideoFile',          'mmAjaxController', 'searchSubtitles' ),
            new ezcMvcRailsRoute( '/ajax/merge-status/:mergeHash',             'mmAjaxController', 'mergeStatus' ),
            new ezcMvcRailsRoute( '/ajax/queue-command',                       'mmAjaxController', 'queueCommand' ),
            new ezcMvcRailsRoute( '/ajax/merge-queue-status',                  'mmAjaxController', 'mergeQueueStatus' ),

            new ezcMvcRailsRoute( '/ajax/downloadsubtitle/:VideoFile/:SubFileId/:SubType/:ZipFileId',
                'mmAjaxController', 'downloadSubtitles' ),
            new ezcMvcRailsRoute( '/ajax/downloadsubtitle/:VideoFile/:SubFileId/:SubType',
                'mmAjaxController', 'downloadSubtitles' ),
            new ezcMvcRailsRoute( '/ajax/generatemergecommand/:VideoFile',
                'mmAjaxController', 'generateMergeCommand' ),
        );
    }
}
?>