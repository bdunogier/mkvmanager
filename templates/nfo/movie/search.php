<? if( count( $this->results ) > 0 ): ?>
<table>
    <? foreach( $this->results as $result ): ?>
    <tr>
        <td width="200"><img width="200" src="<?=$result->thumbnail?>" /></td>
        <td>
            <h3><?= $result->originalTitle ?> (<?= $result->releaseYear ?>)</h3>
            <?if ( isset( $result->url_allocine ) ):?><a href="<?=$result->url_allocine?>">See on allocine.fr</a><br /><?endif?>
            <?if ( isset( $result->url_tmdb ) ):?><a href="<?=$result->url_url_tmdb?>">See on tmdb.org</a><br /><?endif?>
            <br />
            <a href="<?=$result->generateUrl?>">Generate NFO</a><br />
        </td>
    </tr>
    <? endforeach ?>
</table>
<?else:?>
<h3>No result. Sorry.</h3>
<?endif?>
