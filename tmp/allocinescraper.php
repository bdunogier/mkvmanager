<?php
if ( !isset( $_GET['q'] ) and !isset( $_GET['movie'] ) )
{
    exit('q|movie GET parameter missing');
}

// search
if ( isset( $_GET['q'] ) )
{
    $query = $_GET['q'];
    if ( preg_match( '#([^\(]+) \(([0-9]{4})\)#', $query, $queryMatches ) )
    {
        $query = $queryMatches[1];
        $queryYear = $queryMatches[2];
    }
    echo "query: $query<br />";
    echo "queryYear: $queryYear<br />";

    $searchURL = 'http://www.allocine.fr/recherche/?q=' . urlencode( $query );
    $searchPage = file_get_contents( $searchURL );

    $searchPage = substr( $searchPage, strpos( $searchPage, '<h3><b>Films <h4>' ) );
    $searchPage = substr( $searchPage, 0, strpos( $searchPage, '<h3><b>', 1 ) );

    // @todo match more details (year, director, actors, image)
    preg_match_all( '#<a href="/film/fichefilm_gen_cfilm=([0-9]+)\.html" class="link1">(.*?)</a>#', $searchPage, $matches, PREG_SET_ORDER );
    preg_match_all( '#&nbsp;\(([^)]+)\)</a>#', $searchPage, $origTitleMatches, PREG_SET_ORDER );
    preg_match_all( '#<h4 style="color: \#808080">([0-9]{4})</h4>#', $searchPage, $yearMatches, PREG_SET_ORDER );
    foreach ( $matches as $key => $match )
    {
        $movieTitle = strip_tags( $match[2] );
        $originalTitle = strip_tags( $origTitleMatches[$key][1] );
        $year = strip_tags( $yearMatches[$key][1] );
        $perfectMatch = false;
        if ( strtolower( $movieTitle ) == strtolower( $query ) or ( strtolower( $originalTitle ) == strtolower( $query ) ) )
        {
            if ( isset( $queryYear ) )
            {
                if ( $queryYear == $year )
                    $perfectMatch = true;
            }
            else
                $perfectMatch = true;
        }
        elseif ( isset( $queryYear ) && ( $queryYear == $year ) )
        {
            $perfectMatch = true;
        }
        $movieID = $match[1];
        $url = $_SERVER['PHP_SELF'] . '?movie=' . urlencode( $movieID );
        if ( $perfectMatch ) echo "<b>";
        echo "<a href=\"$url\">$movieTitle ($year)</a> (<i>$originalTitle</i>)";
        if ( $perfectMatch ) echo "</b>";
        echo "<br />";
    }
}
// details page
elseif ( isset( $_GET['movie'] ) )
{
    $movieData = array();
    $detailsPage = utf8_encode( file_get_contents( 'http://www.allocine.fr/film/fichefilm_gen_cfilm=' . $_GET['movie'] . '.html' ) );

    // title
    if ( preg_match( '|<h1 class="TitleFilm">([^<]+)</h1>|', $detailsPage, $matches ) )
    {
        $movieData['title'] = $matches[1];
    }

    // original title
    if ( preg_match( '|<h3 class="SpProse">Titre original : <i>([^<]+)</i></h3>|', $detailsPage, $matches ) )
    {
        $movieData['original-title'] = $matches[1];
    }
    else
    {
        $movieData['original-title'] = $movieData['title'];
    }

    // release date
    if ( preg_match( '|<h4>Date de sortie : <b>(.*?)</b>|', $detailsPage, $matches ) )
    {
        $movieData['release-date'] = $matches[1];
    }

    // genre
    $substring = substr( $detailsPage, strpos( $detailsPage, 'Genre : ' ) + 8 );
    $substring = substr( $substring, 0, strpos( $substring, '</h3>' ) );
    if ( preg_match_all( '#<a href="(/film/alaffiche_genre_gen_genre[^"]+)" class="link1">([^<]+)</a>#', $substring, $genreMatches, PREG_SET_ORDER ) )
    {
        foreach( $genreMatches as $genreMatch )
        {
            $movieData['genre'][] = $genreMatch[2];
        }
    }

    // director(s)
    $substring = substr( $detailsPage, strpos( $detailsPage, 'Réalisé par ' ) + 12 );
    $substring = substr( $substring, 0, strpos( $substring, '</h3>' ) );
    if ( preg_match_all( '#<a[^>]*href="([^"]+)"[^>]*>(.*?)</a>#', $substring, $matches, PREG_SET_ORDER ) )
    {
        foreach ( $matches as $match )
        {
            $movieData['directors'][] = array( 'name' => $match[2], 'url' => $match[1] );
        }
    }

    // distributed by...
    if ( strstr( $detailsPage, 'Distribué par ' ) !== false )
    {
        $substring = substr( $detailsPage, strpos( $detailsPage, 'Distribué par ' ) + 14 );
        $substring = substr( $substring, 0, strpos( $substring, '</h3>' ) );
        if ( preg_match_all( '#<a[^>]*href="([^"]+)"[^>]*>(.*?)</a>#', $substring, $matches, PREG_SET_ORDER ) )
        {
            foreach ( $matches as $match )
            {
                $movieData['distributed-by'][] = array( 'name' => $match[2], 'url' => $match[1] );
            }
        }
    }

    // runtime
    if ( preg_match( '#Durée : ([^\.]+).#', $detailsPage, $runtimeMatches ) )
    {
        sscanf( $runtimeMatches[1], '%dh %dmin', $hours, $minutes );
        $movieData['runtime'] = $hours * 60 + $minutes;
    }

    // synopsis
    $substring = substr( $detailsPage, strpos( $detailsPage, '<h2 class="SpBlocTitle" >Synopsis</h2>' ) + 38 );
    // echo "$substring";
    $substring = substr( $substring, strpos( $substring, '<h4>' ) + 4 );
    $substring = substr( $substring, 0, strpos( $substring, '</h4>' ) );
    $movieData['synopsis'] = utf8_decode( $substring );
    unset( $substring );

    // images
    $imagesPage = utf8_encode( file_get_contents( 'http://www.allocine.fr/film/galerievignette_gen_cfilm=' . $_GET['movie'] . '.html' ) );
    if ( preg_match( '#<img id=\'imgNormal\' class=\'photo\' src=\'([^\']+)\'#', $imagesPage, $matches ) )
    {
        $movieData['cover'] = $matches[1];
    }

    // trailers
    if ( preg_match( '#<a href="/video/player_gen_cmedia=([0-9]+)&cfilm=[0-9]+\.html" class="link5">#', $detailsPage, $trailerLinkMatches ) )
    {
        $videoID = $trailerLinkMatches[1];
        $videoListURL = "http://www.allocine.fr/webtv/film.html?cfilm={$_GET['movie']}";
        $trailersListPage = utf8_encode( file_get_contents( $videoListURL ) );
        // $trailersPage = utf8_encode( file_get_contents( 'http://www.allocine.fr/webtv/acvision.asp?nopub=1&emission=&player=ASF&debit=HD&cvid=' . $videoID ) );
        if ( preg_match_all( '#<a href="(acvision\.asp\?cvid=[0-9]+)" [^>]+>([^<]+)</a>#', $trailersListPage, $trailersMatches, PREG_SET_ORDER ) )
        {
            foreach( $trailersMatches as $trailerMatch )
            {
                $trailerLabel = strtolower( utf8_decode( $trailerMatch[2] ) );
                if ( strpos( $trailerLabel, 'annonce' ) !== false )
                {
                    $trailerURL = 'http://www.allocine.fr/webtv/' . $trailerMatch[1] . '&nopub=1&player=ASF';
                    $trailerHTML = utf8_encode( file_get_contents( $trailerURL . '&debit=HD' ) );
                    if ( !$trailerFileURL = GetTrailerFileURL( $trailerHTML ) )
                    {
                        $trailerHTML = utf8_encode( file_get_contents( $trailerURL . '&debit=H' ) );
                        if ( !$trailerFileURL = GetTrailerFileURL( $trailerHTML ) )
                        {
                            continue;
                        }
                    }
                    // $trailerPage = utf8_encode( file_get_contents( $trailerURL ) );
                    $movieData['trailers'][] = array( 'name' => $trailerLabel, 'url' => $trailerFileURL );
                }
            }
        }
    }

    // casting
    $movieData['actors'] = array();
    $castingURL = "http://www.allocine.fr/film/casting_gen_cfilm={$_GET['movie']}.html";
    $castingHTML = utf8_encode( file_get_contents( $castingURL ) );
    $substring = substr( $castingHTML, strpos( $castingHTML, '<h2 class="SpProse" style="color: #D20000; font-weight:bold;">Acteurs</h2>' ) + 74 );
    $substring = substr( $substring, 0, strpos( $substring, '</table' ) );
    if ( preg_match_all( '#<h5><a href="(/personne/fichepersonne_gen_cpersonne=[0-9]+\.html)" class="link1">([^<]+)</a></h5>#', $substring, $actorsMatches, PREG_SET_ORDER ) )
    {
        preg_match_all( '#<h5>([^<]+)</h5>#', $substring, $actorsRolesMatches );
        foreach ( $actorsMatches as $key => $actorMatch )
        {
            $actor = array();
            $actor['link'] = $actorMatch[1];
            $actor['name'] = $actorMatch[2];
            $actor['role'] = $actorsRolesMatches[1][$key];

            // actor role
            $movieData['actors'][] = $actor;
            unset( $actor );
        }
    }

    echo "<b>MOVIE DATA</b><br /><pre>";
    print_r( $movieData );
    echo "</pre>";
}

function GetTrailerFileURL( $trailerHTML )
{
    if ( preg_match( '|<PARAM name="URL" value="([^"]+)">|', $trailerHTML, $trailerGeneratorMatch ) )
    {
        // get generation page to get the MMS URL
        $generationURL = $trailerGeneratorMatch[1];
        $generationHTML = utf8_encode( file_get_contents( $generationURL ) );
        if ( preg_match( '|<REF HREF = "(mms://[^"]+)" />|', $generationHTML, $generationMatch ) )
        {
            $trailerURL = $generationMatch[1];
            $trailerURL = substr( $trailerURL, strpos( $trailerURL, 'mediaplayer.allocine.fr' ) );
            $trailerURL = str_replace( '.wmv', '.flv', $trailerURL );
            $trailerURL = 'http://h.fr.' . $trailerURL;
            return $trailerURL;
        }
    }
    return false;
}
?>