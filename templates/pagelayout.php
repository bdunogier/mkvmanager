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
    var mm = {
        statusBar: {
            /**
             * Status refresh rate, in milliseconds
             * @var int
             */
            refreshRate: 1000,

            /**
             * Current request start time, in milliseconds
             * @var int
             */
            requestStartTime: false,

            updateStatus: function updateStatus() {

                if ( mm.statusBar.requestStartTime != false ) {
                    console.log( "Pending request detected, aborting", mm.statusBar );
                    return;
                }

                mm.statusBar.requestStartTime = new Date().getTime();
                console.log( "No pending request, processing" );
                $.get(
                    '/ajax/daemon/queue-contents/running/Merge',
                    function success( r ) {
                        console.log( "Success", r );
                        var timeout;
                        if ( r.message != 'no-operation')
                        {
                            $("#statuspanel").html(
                                '<progress id="pbStatus" max="100" value="' + r.queue[0].progress + '"></progress>' +
                                '<div id="pbStatusText">'+r.queue[0].title+'</div>');
                            currentTime = new Date().getTime();

                            if ( mm.statusBar.requestStartTime == false )
                                timeout = mm.statusBar.refreshRate;
                            else
                                timeout = mm.statusBar.refreshRate - ( currentTime - mm.statusBar.requestStartTime );

                        }
                        else
                        {
                            $("#statuspanel").html( 'Idle' );
                            timeout = 1000;
                        }

                        mm.statusBar.requestStartTime = false;

                        console.log( "Setting timeout to " + timeout + "ms", mm.statusBar );
                        setTimeout( 'mm.statusBar.updateStatus()', timeout );
                    },
                    'json'
                );
            }
        }
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