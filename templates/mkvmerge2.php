<?php
?>
<html>
<head>
        <title>MKV Merger</title>
        <style type="text/css">
			body {
				margin-left: 20%;
				margin-right: 20%;
			}

			p.error {
				color: red;
			}

			span.filename {
				font-family: Andale Mono, monospace;
				font-size: 80%;
			}

			div.drivesList {
				font-family: Andale Mono, monospace;
				text-align: center;
				width: 100%;
			}

			div.drive {
				float: left;
				text-align: center;
				margin: 10px;
			}

			div.drive img {
				display: inline;
			}
		</style>
</head>
<body>
<h1>MKV Merger</h1>

<frameset>
	<legend>Convert windows CMD</legend>
	<form method="POST" action="<?php echo str_replace('index.php/', '', $_SERVER['REQUEST_URI'] ); ?>">
		<p>
			<textarea name="WinCmd" style="width:100%; height: 200px;"><?php
			if ( isset( $_POST['WinCmd'] ) )
				echo htmlentities( $_POST['WinCmd']);
			?></textarea>
		</p>
		<p>
			<div class="drivesList">
			<?php foreach( $this->targetDisks as $disk ) : ?>
				<div class="drive">
					<div class="name"><?php echo $disk->name ?></div>
					<a href="#"><img src="/images/icons/harddrive.png" width="64" heigh="64" title="Disk: <?php echo $disk->name ?>"/></a>
					<div class="freespace"><?php echo $disk->freespace ?></div>
				</div>
			<?php endforeach; ?>
			</div>
			<p style="clear: both" />
			<p><input type="checkbox" name="QueueCommand" value="1" id="chkQueueCommand" /><label for="chkQueueCommand">Add to queue</label></p>
		</p>
		<p><input type="submit" name="ConvertWinCmd" /></p>
	</form>
	<?php

	if ( isset( $this->command ) ):?>
		<p>
		Titre: <?php echo $this->command->title; ?><br />
		Cible: <?php echo $this->command->target; ?><br />
		Sous titres: <ul>
		<?php foreach( $this->command->subtitleFiles as $file ) {
			echo "<li>{$file}</li>";
		}
		?>
		</ul><br />
		Videos: <ul>
		<?php foreach( $this->command->videoFiles as $file ) {
			echo "<li>{$file}</li>";
		}
		?>
		</ul>
		</p>
		<p style="font-family: monospace;"><?php echo $this->command; ?></p>
	<?php endif; ?>

</frameset>

</body>

