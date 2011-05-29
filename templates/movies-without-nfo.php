<h1>Movies without NFO</h1>
<ul><? foreach( $this->movies as $movie ) :?>
    <li><?=htmlspecialchars( $movie )?> <a href="/nfo/movie/search/<?=urlencode( htmlspecialchars( $movie ) )?>">search</a></li>
<? endforeach?></ul>
