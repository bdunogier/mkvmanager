<html>
    <head>
    <title>Movies without NFO :: Media manager</title>
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
    </style>
    </head>
    <body>
    <h1>Movies without NFO</h1>
    <ul><? foreach( $this->movies as $movie ) :?>
        <li><?=htmlspecialchars( $movie )?></li>
    <? endforeach?></ul>
    </body>
</html>