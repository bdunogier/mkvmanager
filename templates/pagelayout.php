<html>
    <head>
    <title><?=$this->page_title?></title>
    <style type="text/css">
    @import url('/css/global.css');
    @import url('/css/jquery.lightbox-0.5.css');
    </style>

    <script type="text/javascript" src="/js/jquery-1.4.2.min.js"></script>
    <script type="text/javascript" src="/js/jquery.bpopup-0.4.1.min.js"></script>
    <script type="text/javascript">
    var mm = {};
    mm.statusBar = {};
    mm.statusBar.processing = false;
    mm.statusBar.updateStatus = function updateStatus()
    {
        if ( mm.statusBar.processing == true ) {
            return
        }
        mm.statusBar.processing = true;
        $.get(
            '/ajax/merge-active-status',
            function success( r ) {
                var timeout;
                if ( r.message != 'no-operation')
                {
                    $("#statuspanel").html(
                        '<progress id="pbStatus" max="100" value="' + r.progress + '"></progress>' +
                        '<div id="pbStatusText">'+r.file+'</div>');
                    timeout = 50;
                }
                else
                {
                    $("#statuspanel").html( 'Idle' );
                    timeout = 1000;
                }
                mm.statusBar.processing = false;
                setTimeout( 'mm.statusBar.updateStatus()', timeout );
            }, 'json' );
    };

    $(document).ready(function() {
        mm.statusBar.updateStatus();
    });
    </script>
    </head>
    <body>
    <div id="centeredmenu">
      <ul>
        <li><a href="/tvdashboard">TV Dashboard</a></li>
        <li><a href="/movies">Movies</a></li>
        <li><a href="/mkvmerge">MKV Merger</a></li>
        <li><a href="/subtitles">Subtitles</a></li>
        <li><a href="/movies-without-nfo">Movies without NFO</a></li>
        <li><a href="/merge-queue/active">Current merge queue</a></li>
        <li><a href="/merge-queue/done">Finished operations</a></li>
        <li><a href="/merge-queue/archive">Merge queue archive</a></li>
      </ul>
    </div>

    <div id="content">
    <?=$this->content?>
    </div>
    <div id='statuspanel'></div>
    </body>
</html>