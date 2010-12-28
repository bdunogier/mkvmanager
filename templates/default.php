<html>
    <head>
    <title>Media manager</title>
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
    <h1>Default template</h1>

    <h2>Request</h2>
    <pre><?
    print_r( $this->__request)
    ?></pre>

    <h2>Variables</h2>
    <pre><?php
    $variables = $this->variables;
    unset( $variables['__request'] );
    print_r( $variables );
    ?></pre>
    </body>
</html>