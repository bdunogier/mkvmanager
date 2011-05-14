<h1>Trailer for allocine movie #<?=$this->allocine_id?></h1>

<ul>
<?foreach ( $this->trailers as $trailer ):?>
    <li><a href="<?=$trailer->href?>" title="<?=htmlentities( utf8_decode( $trailer->title ) )?>"><?=htmlentities( utf8_decode( $trailer->title ) )?></a></li>
<?endforeach?>
</ul>