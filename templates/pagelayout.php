<html>
    <head>
    <title>[[title]]</title>
    <style type="text/css">
    html {
        padding: 0;
        margin: 0;
        color: #000;
        background: #fff;
    }

    body {
        width: 100%;
        height: 100%;
    }

    #content {
        float: left;
    }

    p.error {
        color: red;
    }

    span.filename {
        font-family: Andale Mono, monospace;
        font-size: 80%;
    }

    #content {
        margin-left: 250px;
        padding: 10px 10px;
    }

    /* menu */
    #menu {
        width: 200px;
        border-style: solid solid none solid;
        border-color: #94AA74;
        border-size: 1px;
        border-width: 1px;
        margin: 10px;
        float:left;
        overflow: auto;
    }
    #menu ul {
    list-style: none;
    margin: 0;
    padding: 0;
    }
    #menu li a {
    height: 32px;
    voice-family: "\"}\"";
    voice-family: inherit;
    height: 24px;
    text-decoration: none;
    }

    /* N'oubliez pas de renseigner l'adresse des images que vous avez téléchargé */
    #menu li a:link, #menu li a:visited {
    color: #5E7830;
    display: block;
    background: url(images/menu1.gif);
    padding: 8px 0 0 10px;
    }

    #menu li a:hover {
    color: #26370A;
    background: url(images/menu1.gif) 0 -32px;
    padding: 8px 0 0 10px;
    }

    #menu li a:active {
    color: #26370A;
    background: url(images/menu1.gif) 0 -64px;
    padding: 8px 0 0 10px;
    }
    </style>
    </head>
    <body>
    <div id="container">
        <div id="menu">
          <ul>
            <li><a href="/mkvmerge2">MKV Merger</a></li>
            <li><a href="/subtitles">Subtitles</a></li>
            <li><a href="/movies-without-nfo">Movies without NFO</a></li>
          </ul>
        </div>
        <div id="content">
        <?=$this->content?>
        </div>
    </div>
    </body>
</html>