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
