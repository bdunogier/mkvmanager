<?php
class mmMvcRouter extends ezcMvcRouter
{
    public function createRoutes()
    {
        return array(
            new ezcMvcRailsRoute( '/',          'mmMkvManagerController', 'mkvMerge' ),
            new ezcMvcRailsRoute( '/test',      'mmMkvManagerController', 'test' ),
            new ezcMvcRailsRoute( '/fatal',     'mmMkvManagerController', 'fatal' ),
            new ezcMvcRailsRoute( '/mkvmerge',  'mmMkvManagerController', 'mkvMerge' ),
            new ezcMvcRailsRoute( '/mkvmerge2', 'mmMkvManagerController', 'mkvMerge' ),
            new ezcMvcRailsRoute( '/subtitles', 'mmMkvManagerController', 'subtitles' ),

            new ezcMvcRailsRoute( '/ajax/mkvmerge',                            'mmAjaxController', 'mkvMerge' ),
            new ezcMvcRailsRoute( '/ajax/bestfit',                             'mmAjaxController', 'bestFit' ),
            new ezcMvcRailsRoute( '/ajax/searchsubtitles/:VideoFile',          'mmAjaxController', 'searchSubtitles' ),

            new ezcMvcRailsRoute( '/ajax/downloadsubtitle/:VideoFile/:SubFileId/:SubType/:ZipFileId',
                'mmAjaxController', 'downloadSubtitles' ),
            new ezcMvcRailsRoute( '/ajax/downloadsubtitle/:VideoFile/:SubFileId/:SubType',
                'mmAjaxController', 'downloadSubtitles' ),
            );
    }
}
?>