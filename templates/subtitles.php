<html>
    <head>
    <title>Media manager</title>
    <style type="text/css">
        body {
            margin-left: 20%;
            margin-right: 20%;
        }

        p.error {
            color: red;
        }

        span.filename {
            font-family: Andale Mono, monospace;
            font-size: 80%;
        }
    </style>
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
    <script type="text/javascript">
    $(document).ready(function() {
        // On video file click
        $(".VideoFile").click(function() {
            console.log(  );
            // search for this episode subtitles
            $.get( "/ajax/searchsubtitles/" + $(this).html(), function success( data ) {
                console.log( data.test );
            }, "json" );

        });

    });
    </script>
    </head>
    <body>
    <h1>Subtitles management</h1>

    <? foreach ( $this->VideoFiles as $file ): ?>
        <!--
        Upon a click on the VideoFile li item:
        - show a 'searching' animated icon
        - once fetch is complete (AJAX), list the matching files under the list item (within the li tag)
        - each link will just download the file to the same folder as the episode
        - the episode is turned green, and disappears after a while
        -->
        <li class="VideoFile"><?=$file?></li>
    <?php endforeach ?>
    </body>
</html>