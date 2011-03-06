<style type="text/css">
/* LATEST ADDITIONS */
a {
    color: black;
}

a:active {
    color: black;
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
/* END LATEST ADDITIONS */

/* SHOW LISTING */
div.showContainer img {
    float: left;
    border: 1px solid black;
}
div.showDetails {
    margin-left: 125px;
}

.showContainer {
    padding: 10px;
    height: 150px;
    background-color: #eee;
}
/* END SHOW LISTING */

#listingWrapper br {
    clear: both;
}
</style>

<script type="text/javascript" src="js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="js/jquery-ui-1.8.6.custom.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
}
</script>
<h1>TV Dashboard</h1>

<h2>Latest additions</h2>
<ul id="latestAdditions" class="commaList">
    <?foreach( $this->latest as $latest):?>
    <li><strong><a href="#<?=anchorLink($latest->showName)?>"><?=$latest->showName?></a></strong> S<?=$latest->seasonNumber?>E<?=$latest->episodeNumber?></li>
    <?endforeach;?>
</ul>

<h2>Items requiring attention</h2>
<ul id="showListingSummary" class="commaList">
    <?foreach( $this->shows as $showName => $episodeFiles ):?>
    <li><a href="#<?=anchorLink($showName)?>"><?=$showName?></a> (<?=count($episodeFiles)?>)</li>
    <?endforeach;?>
</ul>
<br />
<div id="outterWrapper">
    <div id="listingwrapper">
    <? foreach( $this->shows as $showName => $episodeFiles ): ?>
        <a name="<?=anchorLink($showName)?>"></a>
        <div class="listingItem">
        <div class="showContainer">
            <img src="/tvshow/image/<?=$showName?>:folder.jpg" height="150" />
            <div class="showDetails">
                <h3><?=$showName?></h3>
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
        </div>
        <br />
    <? endforeach ?>
    </div>
</div>

<?php
function anchorLink( $showName )
{
    return preg_replace( '/[^a-z0-9]/i', '', $showName );
}
?>