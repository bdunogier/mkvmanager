<style type="text/css">
ul#latestAdditions {
    list-style-type: none; margin: 0; padding: 0;
}
ul#latestAdditions li {
    display: inline;
}
ul#latestAdditions li:after {
    content: ", ";
}
ul#latestAdditions li:last-child:after {
    content: ".";
}
ul#latestAdditions
 {
    content: ".";
}
#show img {
    float: left;
    border: 1px solid black;
}
#show ul {
    margin-left: 120px;
}
#listingWrapper br {
    clear: both;
}
</style>
<h1>TV Dashboard</h1>

<h2>Latest additions</h2>
<ul id="latestAdditions">
    <?foreach( $this->latest as $latest):?>
    <li><?=$latest->fullname?></li>
    <?endforeach;?>
</ul>

<h2>Items requiring attention</h2>
<div id="outterWrapper">
    <div id="listingwrapper">
    <? foreach( $this->shows as $showName => $episodeFiles ): ?>
        <div class="listingItem">
        <div id="show">
            <a name="<?=$showName?>"></a>
            <h3><?=$showName?></h3>
            <img src="/tvshow/image/<?=$showName?>:folder.jpg" width="120" />
            <ul>
            <? $displayed = 0; ?>
            <? foreach( $episodeFiles as $episodeFile ): ?>
                <li>Episode <?=$episodeFile->seasonNumber?>x<?=$episodeFile->episodeNumber?>: <?=$episodeFile->episodeName?></li>
                <? if ( ++$displayed == 3 && count( $episodeFiles ) > 3 ):
                   $others = count( $episodeFiles ) - $displayed; ?>
                <li>... and <?=$others?> more</li>
                <? break; endif; ?>
            <? endforeach ?>
            </ul>
        </div>
        </div>
        <br />
    <? endforeach ?>
    </div>
</div>