<style type="text/css">
progress {
    width: 1000px;
}
</style>
<h1>Save NFO for '<?=$this->movie?>'</h1>

<? foreach( $this->operations as $operation): ?>
    <div class="operation" style="display: block">
        <h3><?=$operation->title?></h3>
        <progress class="operationProgress" id="progress_<?=$operation->hash?>" max="100" />
        <input type="hidden" name="progress_hash_<?=$operation->hash?>" class="progress_hash" value="<?=$operation->hash?>" />
    </div>
<? endforeach ?>

<script type="text/javascript" src="/js/jquery-1.4.2.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
    hashes = $("input[type='hidden'].progress_hash" ).map( function(){
        return this.value;
    }).get().join(',');

    var mm = {};
    mm.progress = {};
    mm.progress.processing = false;
    mm.progress.updateStatus = function updateStatus()
    {
        if ( mm.progress.processing == true ) {
            return;
        }

        mm.progress.processing = true;

        $.get(
            '/daemon/progress/' + hashes,
            function success( r ) {
                console.log();
            }, 'json'
        );
    }
});
</script>