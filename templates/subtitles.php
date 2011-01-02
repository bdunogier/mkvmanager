<style type="text/css">
    /* li { display: inline; } li:not(:last-child):after { content: ", "; } */

    /*demo page css*/
    .demoHeaders { margin-top: 2em; }
    #dialog_link {padding: .4em 1em .4em 20px;text-decoration: none;position: relative;}
    #dialog_link span.ui-icon {margin: 0 5px 0 0;position: absolute;left: .2em;top: 50%;margin-top: -8px;}
    ul#icons {margin: 0; padding: 0;}
    ul#icons li {margin: 2px; position: relative; padding: 4px 0; cursor: pointer; float: left;  list-style: none;}
    ul#icons span.ui-icon {float: left; margin: 0 4px;}
</style>
<script type="text/javascript" src="js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="js/jquery-ui-1.8.6.custom.min.js"></script>
<link type="text/css" href="css/trontastic/jquery-ui-1.8.6.custom.css" rel="stylesheet" />
<script type="text/javascript">
$(document).ready(function() {

    $("#main").accordion({ header: "h3" });

    /**
    * Fetch subtitles list for episode and shows the confirmation message
    */
    $(".VideoFile > a.SearchSubtitles").click(function(e) {
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
                // @todo Make this more javascript like, and use a method that automatically
                // adds a link to the image file
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
    * Fetch subtitles list for episode and shows the confirmation message
    */
    $(".VideoFile > a.GenerateCommand").click(function(e) {
        e.preventDefault();

        // set waiting text
        var statusDiv = $(this).siblings( 'div.VideoStatusText' );
        statusDiv.html( 'Generating command...' );

        var targetDiv = $(this).siblings('div.CommandPlaceHolder');

        // @todo search for this episode subtitles
        $.get( $(this).attr('href'), function success( data ) {
            console.log( $(this) );
            html = data.command;
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

<div id="main">
    <div>
        <h3><a href="#">Episodes without subtitles</a></h3>
        <div>
        <? foreach ( $this->VideoFiles as $file ): ?>
            <li class="VideoFile"><?=$file?>
                <a class="SearchSubtitles" href="/ajax/searchsubtitles/<?=rawurlencode( $file )?>">Fetch subtitles list</a>
                &nbsp;|&nbsp;
                <a class="GenerateCommand" href="/ajax/generatemergecommand/<?=rawurlencode( $file )?>">Generate merge command</a>
                <div class="VideoStatusText"></div>
                <div class="SubtitlesPlaceHolder hidden"></div>
                <div class="CommandPlaceHolder hidden"></div>
            </li>
        <?php endforeach ?>

        </div>
    </div>
    <div>
        <h3><a href="#">Files with subtitles</a></h3>
        <div>TODO</div>
    </div>
</div>
