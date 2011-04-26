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

#MoviePopup {
    display: none;
    background-color: white;
    width: 600px;
    height: 600px;
    border: 2px solid black;
    border-radius: 10px;
    padding: 5px;
    overflow: auto;
}
</style>

<script type="text/javascript" src="/js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="/js/jquery.bpopup-0.4.1.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {

$(".movieFolder").bind( 'click', function(e) {
    e.preventDefault();

    // popup the overlay
    bPopup = $("#MoviePopup").bPopup({opacity:'0.5'});
    targetDiv = $("#MoviePopup");
    releaseName = $(this).text();

    targetDiv.html( '<h3>' + releaseName + '</h3>' );
    targetDiv.append( '<h4>Movies</h4>' );

    // @todo search for this episode subtitles
    $.get( $(this).attr('href'), function success( r ) {
        if ( r.status == 'ok' )
        {
            targetDiv.append( '<ul>' );
            for ( index in r.movies )
            {
                movie = r.movies[index];
                targetDiv.append( '<li><a href="/ajax/movie-search-releases/' + movie.id + '" class="movieId">' + movie.title + ' (' + movie.info + ')</a></li>' );
            }
            targetDiv.append( '</ul>' );
        }
        else
        {
            targetDiv.append( r.message );
        }
    }, "json" );

    return false;

});

$(".movieId").live( 'click', function(e) {
    e.preventDefault();

    // popup the overlay
    targetDiv = $("#MoviePopup");
    targetDiv.append( '<h4>Releases</h4>' );

    // @todo search for this episode subtitles
    $.get( $(this).attr('href'), function success( r ) {
        if ( r.status == 'ok' )
        {
            targetDiv.append( '<ul>' );
            for ( index in r.releases )
            {
                release = r.releases[index];
                targetDiv.append( '<li><a href="/ajax/movie-search-subtitles/' + release.id + '" class="movieRelease">' + release.title + '</a></li>' );
            }
            targetDiv.append( '</ul>' );
        }
        else
        {
            targetDiv.append( r.message );
        }
    }, "json" );

    return false;

});

$(".movieRelease").live( 'click', function(e) {
    e.preventDefault();

    // popup the overlay
    targetDiv = $("#MoviePopup");
    targetDiv.append( '<h4>Subtitles</h4>' );

    // @todo search for this episode subtitles
    $.get( $(this).attr('href'), function success( r ) {
        if ( r.status == 'ok' )
        {
            targetDiv.append( '<ul>' );
            for ( index in r.subtitles )
            {
                subtitle = r.subtitles[index];
                targetDiv.append( '<li>' + subtitle + '</a></li>' );
            }
            targetDiv.append( '</ul>' );
        }
        else
        {
            targetDiv.append( r.message );
        }
    }, "json" );

    return false;

});

});
</script>
<ul>
    <? foreach( $this->movies as $movie ): ?>
    <li><a class="movieFolder" href="/ajax/movie-search/<?=urlencode( $movie)?>"><?=$movie?></a></li>
    <? endforeach ?>
</ul>

<div id="MoviePopup"></div>
