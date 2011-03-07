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
</style>

<script type="text/javascript" src="js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="js/jquery-ui-1.8.6.custom.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
}
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
<? endforeach ?>

<?php
function anchorLink( $showName )
{
    return preg_replace( '/[^a-z0-9]/i', '', $showName );
}
?>