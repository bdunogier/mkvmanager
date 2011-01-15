<html>
    <head>
    <title><?=$this->page_title?></title>
    <style type="text/css">
    @import url(/css/global.css);
    </style>

    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
    <script type="text/javascript">
    var processing = false;
    function updateStatus()
    {
        if ( processing == true ) return;
        processing = true;
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
                processing = false;
                setTimeout( 'updateStatus()', timeout );
            }, 'json' );
    }
    $(document).ready(function() {
        updateStatus();
    });
    this.getSidebar = function()
    {
        return 'test';
    }
    </script>
    </head>
    <body>
    <div id="centeredmenu">
      <ul>
        <li><a href="/mkvmerge">MKV Merger</a></li>
        <li><a href="/subtitles">Subtitles</a></li>
        <li><a href="/movies-without-nfo">Movies without NFO</a></li>
        <li><a href="/merge-queue">Merge queue status</a></li>
      </ul>
    </div>

    <div id="content">
    <?=$this->content?>
    </div>
    <div id='statuspanel'></div>
    </body>
</html>