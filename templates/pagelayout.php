<html>
    <head>
    <title><?=$this->page_title?></title>
    <style type="text/css">
    html {
        padding: 0;
        margin: 0;
        color: #000;
        background: #fff;
    }

    body {
        width: 100%;
        height: 100%;
    }

    /* BEGIN centered menu */
    #centeredmenu {
       float:left;
       width:100%;
       background:#fff;
       border-bottom:4px solid #000;
       overflow:hidden;
       position:relative;
    }
    #centeredmenu ul {
       clear:left;
       float:left;
       list-style:none;
       margin:0;
       padding:0;
       position:relative;
       left:50%;
       text-align:center;
    }
    #centeredmenu ul li {
       display:block;
       float:left;
       list-style:none;
       margin:0;
       padding:0;
       position:relative;
       right:50%;
    }
    #centeredmenu ul li a {
       display:block;
       margin:0 0 0 1px;
       padding:3px 10px;
       background:#ddd;
       color:#000;
       text-decoration:none;
       line-height:1.3em;
    }
    #centeredmenu ul li a:hover {
       background:#369;
       color:#fff;
    }
    #centeredmenu ul li a.active,
    #centeredmenu ul li a.active:hover {
       color:#fff;
       background:#000;
       font-weight:bold;
    }    /* END centered menu */

    p.error {
        color: red;
    }

    span.filename {
        font-family: Andale Mono, monospace;
        font-size: 80%;
    }

    #content {
        padding: 40px;
    }

    #statuspanel {
    background:orange none repeat scroll 0 0;
    border-top:1px solid black;
    bottom:0px;
    padding:5px;
    position:fixed;
    width:100%;
    z-index:100;
}
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
                    $("#statuspanel").html( '<progress max="100" value="' + r.progress + '" style="width: 90%"/> ' + r.file );
                    timeout = 50;
                }
                else
                {
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