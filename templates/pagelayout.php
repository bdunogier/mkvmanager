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
                '/ajax/daemon/queue-contents/running/Merge',
                function success( r ) {
                var timeout;
                if ( r.message != 'no-operation')
                {
                    $("#statuspanel").html(
                        '<progress id="pbStatus" max="100" value="' + r.queue[0].progress + '"></progress>' +
                        '<div id="pbStatusText">'+r.queue[0].title+'</div>');
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
        <?foreach ( $this->variables['top_menu'] as $topMenuItem ): ?>
        <li><a href="<?=$topMenuItem['url']?>"><?=$topMenuItem['text']?></a></li>
        <?endforeach?>
      </ul>
    </div>

    <div id="content">
    <?=$this->content?>
    </div>
    <div id='statuspanel'></div>
    </body>
</html>