<pre>
<?php
print_r( $this->usage )
?>
</pre>
<?php
$types = array();
foreach( $this->usage as $usage )
{
    foreach( array_keys( $usage ) as $type )
    {
        if ( !in_array( $type, $types ) )
            $types[] = $type;
    }
}
natsort( $types );
?>

<table>
    <caption>Disk usage by type</caption>
    <tr>
        <th>Disk</th>
        <?php foreach( $types as $type ) : ?>
        <th><?=$type?></th>
        <?php endforeach ?>
    </tr>
    <?php foreach( $this->usage as $disk => $usage ) : ?>
    <tr>
        <td><?=$disk?></td>
        <?php foreach( $usage as $type => $space ) : ?>
        <td><?=$space?></td>
        <?php endforeach ?>
    </tr>
    <?php endforeach ?>
</table>