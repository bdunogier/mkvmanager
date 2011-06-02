<script type="text/javascript" src="/js/jquery-1.4.2.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
});
</script>
<h1><?=$this->infos->originalTitle?></h1>

<h2>Trailers</h2>
<table>
    <?foreach( $this->infos->trailers as $trailerIndex => $trailer):?>
    <tr>
        <td><a href="<?=(string)$trailer?>"><?=(string)$trailer?></a></td>
        <td><?=$trailer->title?></td>
        <td><form method="get" action="<?=$_SERVER['PHP_SELF']?>">
            <input type="button" name="ActionType" value="SelectTrailer" />
            <input type="hidden" name="ActionValue" value="<?=$trailerIndex?>" />
        </form></td>
    </tr>
    <?endforeach?>
</table>

<h2>Posters</h2>
<h2>Fanarts</h2>

<h2>NFO</h2><pre id="nfo"><?=htmlentities( utf8_decode( $this->nfo ) )?></pre>

<form method="post" action="<?=$this->saveUrl?>">
    <input type="submit" value="Save NFO" />
</form>
</h1>