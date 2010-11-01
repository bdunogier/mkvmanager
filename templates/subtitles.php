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
        .hidden {
            display: none;
        }
    </style>
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
    <script type="text/javascript">
    $(document).ready(function() {

        /**
        * On video file click
        * Fetch subtitles list for episode and shows the confirmation message
        */
        $(".VideoFile > a").click(function(e) {
            e.preventDefault();

            // set waiting text
            var statusDiv = $(this).siblings( 'div.VideoStatusText' );
            statusDiv.html( 'Fetching subtitles...' );

            var targetDiv = $(this).siblings('div.SubtitlesPlaceHolder');

            // @todo search for this episode subtitles
            $.get( $(this).attr('href'), function success( data ) {
                console.log( $(this) );
                html = '<ul>';
                for ( index in data.data )
                {
                    item = data.data[index];
                    html += '<li><a class="SubtitleDownloadLink" href="' + item.link + '">' + item.name + '</a><div class="SubtitleStatusText hidden"></div></li>';
                }
                html += '</ul>';
                targetDiv.append( html );
                targetDiv.show();

                statusDiv.html( 'Subtitles fetched successfully' );
            }, "json" );

            // @todo hide waiting text & set status text: Fetched X subtitles
        });

        /**
        * Click event on download subtitle link
        * Calls the AJAX subtitle download callback, and displays a confirmation
        * message when done
        */
        $(".SubtitleDownloadLink").live( 'click', function(e) {
            e.preventDefault();
            var statusDiv = $(this).siblings( 'div.SubtitleStatusText' );

            statusDiv.html( 'Downloading file...' );
            statusDiv.show();

             // Start subtitle download
             $.get( $(this).attr('href'), function success( data ) {
                statusDiv.html( 'File downloaded to ' + data.path );
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
        <li class="VideoFile"><a href="/ajax/searchsubtitles/<?=rawurlencode( $file )?>"><?=$file?></a><div class="VideoStatusText"></div>
            <div class="SubtitlesPlaceHolder hidden"></div>
        </li>
    <?php endforeach ?>
    </body>
</html>