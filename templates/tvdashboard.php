<style type="text/css">
/* LATEST ADDITIONS */
a {
    color: black;
}

a:active {
    color: black;
}

h1 {
    text-align: center;
}

ul.commaList {
    list-style-type: none; margin: 0; padding: 0;
}
ul.commaList li {
    display: inline;
}
ul.commaList li:after {
    content: ", ";
}
ul.commaList li:last-child:after {
    content: ".";
}
ul.commaList
{
    content: ".";
}
ul.commaList a {
    text-decoration: none;
}
ul.commaList a:hover {
    text-decoration: underline;
}

div#containerLatestAdditions {
    width: 30%;
    float: left;
}
div#containerShowListingSummary {
    padding-left: 50px;
    width: 60%;
    float: left;
}

/* SHOW LISTING */
div.listingItem {
    width: 32%;
    margin: 5px;
    float: left;
    border: 1px solid black;
}

div.showContainer {
    padding: 8px;
    height: 130px;
    background-color: #eee;
}

div.showDetails {
    margin-left: 100px;
}

div.showContainer img {
    float: left;
    border: 0px solid black;
}

.showDetails h3 {
    margin-top: 0px;
}
/* END SHOW LISTING */

br {
    clear: both;
}
#SubtitlesOverlay {
    display: none;
    background-color: white;
    width: 600px;
    height: 600px;
    border: 2px solid black;
    border-radius: 10px;
    padding: 5px;
    overflow: auto;
}

#CommandOverlay {
    display: none;
    background-color: white;
    width: 600px;
    height: 600px;
    border: 2px solid black;
    border-radius: 10px;
    padding: 5px;
    overflow: auto;
}

ul.listEpisodes {
    overflow: hidden;
}

ul.listEpisodes li {
    white-space: nowrap;
}

ul.icon {
  list-style-type: none;
  padding: 0;
  margin-left: 0;
}
ul.icon li {
  background-repeat: no-repeat;
  padding-left: 18px;
}
ul.listEpisodes li.nosubtitle {
  background-image: url('images/icons/redcross_16x16.png');
}
ul.listEpisodes li.subtitle {
  background-image: url('images/icons/subtitles_16x16.png');
}
/*ul.listEpisodes li.subtitle a.generateCommand {
  display: visible;
}
ul.listEpisodes li.nosubtitle a.generateCommand {
  display: none;
}*/
/* list item with loading animation - needs ul.icon class */
ul.icon li.loading {
  background-image: url('images/icons/loading_16x16.gif');
}
</style>

<script type="text/javascript" src="/js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="/js/jquery.bpopup-0.4.1.min.js"></script>
<script type="text/javascript">
// li item we are editing
currentEpisode = false;
bPopup = false;

$(document).ready(function() {
    /**
     * Click on an episode
     * Fetch and show available subtitles
     */
    $(".episode").bind('click', function(e) {
        e.preventDefault();

        var episodeName = $(this).parent().text();
        var releaseName = $(this).attr( 'title' );
        currentEpisode = $(this).parent();

        // popup the overlay
        bPopup = $("#SubtitlesOverlay").bPopup({opacity:'0.5'});

        var targetDiv = $("#SubtitlesOverlay");

        // set waiting text
        targetDiv.html( '<h3>' + episodeName + '</h3>' );
        targetDiv.append( '<h4>' + releaseName + '</h4>' );
        targetDiv.append('<p id="tmpFetchingSubtitles">Fetching subtitles...</p>');

        // @todo search for this episode subtitles
        $.get( $(this).attr('href'), function success( data ) {
            $('#tmpFetchingSubtitles').remove();
            if ( data.status == 'ok' )
            {
                $('#tmpFetchingSubtitles').remove();
                // @todo refactor this, it is ridiculous :)
                html = '<h5>Valid subtitles</h5>';
                html += '<ul class="icon">';
                for ( index in data.subtitles )
                {
                    // @todo Make this more javascript like, and use a method that automatically
                    // adds a link to the image file
                    item = data.subtitles[index];

                    // @todo: only hide bad priorities, with option to toggle
                    if ( item.priority >= 0 )
                    {
                        html += '<li><a title="S:' + item.originSite + ', P:' + item.priority + '" class="SubtitleDownloadLink" href="' + item.link + '">';
                        html += item.name;
                        html += '</a>';
                        // html += <div class="SubtitleStatusText hidden"></div></li>';
                    }
                }
                html += '</ul>';
                html += '<h5>Invalid subtitles</h5>';
                html += '<ul class="icon">';
                for ( index in data.subtitles )
                {
                    // @todo Make this more javascript like, and use a method that automatically
                    // adds a link to the image file
                    item = data.subtitles[index];

                    // @todo: only hide bad priorities, with option to toggle
                    if ( item.priority < 0 )
                    {
                        html += '<li><a class="SubtitleDownloadLink" href="' + item.link + '">';
                        html += item.name;
                        html += ' (' + item.priority + ')';
                        html += '</a>';
                        // html += <div class="SubtitleStatusText hidden"></div></li>';
                    }
                }
                html += '</ul>';
                targetDiv.append( html );
                targetDiv.show();
            }
            else if ( data.status == 'ko' )
            {
                if ( data.message == 'nosubtitles' )
                {
                    targetDiv.append( 'No subtitles available for this episode' );
                }
                else
                {
                    targetDiv.append( 'Unknown error: ' + data.message );
                }
            }
        }, "json" );

        return false;
    });

    /**
     * Click on a download subtitle link from the popup
     * Call the download href with ajax and download the subtitle file
     */
    $(".SubtitleDownloadLink").live( 'click', function(e) {
        e.preventDefault();
        $(this).parent().addClass( 'loading' );

         // Start subtitle download
         $.get( $(this).attr('href'), function success( data ) {
            if ( currentEpisode.hasClass( 'nosubtitle' ) )
            {
                bPopup.close();
                bPopup = false;
                currentEpisode.removeClass( 'nosubtitle' );
                currentEpisode.addClass( 'subtitle' );
            }
        }, "json" );
    });

    /**
     * Click on the generate command link from the dashboard for a file that has a subtitle
     * Open the popup, and show the generated commandd
     */
    $(".generateCommand").bind('click', function(e) {
        e.preventDefault();

        var targetDiv = $("#CommandOverlay");

        // popup the overlay
        bPopup = $("#CommandOverlay").bPopup({opacity:'0.5'});
        $.get( $(this).attr('href'), function success( data ) {
            if ( data.status == 'ok' )
            {
                $("#CommandOverlay > #CommandPlaceholder").html( data.command );
                $("#CommandOverlay > .Controls").show();
            }
            else if ( data.status == 'ko' )
            {
                $("#CommandOverlay > #CommandPlaceholder").html( data.message );
            }
        }, "json" );

        return false;
    });

        // Add converted merge to the queue
    $("#BtnQueueOperation").live( 'click', function() {
        var Command = $("#CommandOverlay > #CommandPlaceholder").text();
        $.post( "/ajax/queue-command", { MergeCommand: Command },
        function success( data ) {
            $("#BtnQueueOperation").val( "Done" );
	        bPopup.close();
            $("#BtnQueueOperation").val( "Queue" );
        }, "json" );
    });

});
</script>

<div id="containerLatestAdditions">
<h2>Latest additions</h2>
<ul id="latestAdditions" class="commaList">
    <?foreach( $this->latest as $latest):?>
    <li><strong><a href="#<?=anchorLink($latest->showName)?>"><?=$latest->showName?></a></strong> S<?=$latest->seasonNumber?>E<?=$latest->episodeNumber?></li>
    <?endforeach;?>
</ul>
</div>

<div id="containerShowListingSummary">
<h2>Items requiring attention</h2>
<ul id="showListingSummary" class="commaList">
    <?foreach( $this->shows as $showName => $episodeFiles ):?>
    <li><a href="#<?=anchorLink($showName)?>"><?=$showName?></a> (<?=count($episodeFiles)?>)</li>
    <?endforeach;?>
</ul>
</div>

<br />
<? foreach( $this->shows as $showName => $episodeFiles ): ?>
    <a name="<?=anchorLink($showName)?>"></a>
    <div class="listingItem">
    <div class="showContainer">
        <img src="/tvshow/image/<?=$showName?>:folder.jpg" height="130" />
        <div class="showDetails">
            <h3><?=$showName?></h3>
            <ul class="icon listEpisodes">
            <? $displayed = 0; ?>
            <? foreach( $episodeFiles as $episodeFile ): ?>
                <li id="li<?=ucfirst( anchorLink( $episodeFile->filename ) )?>"class="<?=( $episodeFile->hasSubtitleFile ? 'subtitle' : 'nosubtitle' )?>">
                    Episode <a class="episode"
                        title="Downloaded release: <?=htmlentities( (string)$episodeFile->downloadedFile )?> (<?=$episodeFile->downloadedFile->releaseGroup ? $episodeFile->downloadedFile->releaseGroup : 'none'?>)"
                        href="/ajax/searchsubtitles/<?=rawurlencode( $episodeFile->filename )?>/<?=$episodeFile->downloadedFile != '' ? rawurlencode( $episodeFile->downloadedFile ) : 'none' ?>">
                        <?=$episodeFile->seasonNumber?>x<?=$episodeFile->episodeNumber?>: <?=$episodeFile->episodeName?></a>
                        <a class="generateCommand" href="/ajax/generate-command/<?=rawurlencode( $episodeFile->filename )?>">mkvmerge</a>
                </li>
                <? if ( ++$displayed == 5 && count( $episodeFiles ) > 3 ):
                   $others = count( $episodeFiles ) - $displayed; ?>
                <li>... and <?=$others?> more</li>
                <? break; endif; ?>
            <? endforeach ?>
            </ul>
        </div>
    </div>
    </div>
<? endforeach ?>

<div id="SubtitlesOverlay">Subtitles go here</div>
<div id="CommandOverlay">
    <h5>Command</h5>
    <div id="CommandPlaceholder"></div>
    <div class="Controls" style="display: none">
        <input type="button" id="BtnQueueOperation" value="Queue" />
    </div>
</div>

<?php
function anchorLink( $showName )
{
    return preg_replace( '/[^a-z0-9]/i', '', $showName );
}
?>
