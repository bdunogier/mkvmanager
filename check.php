<?php

/*
    This is just a list of test that need to be done at startup
    @todo rewrite using Exceptions & eZComponents functions
*/

// TESTS
// -- PHP Extensions needed
$phpExtensions = array( 'pdo_sqlite' );
$phpExtensionsFailure = array();
foreach( $phpExtensions as $extensionName )
{
    if( !extension_loaded( $extensionName ) )
    {
        $phpExtensionsFailure[]=$extensionName;
    }
}
// -- Directory persmissions
$writableDirectories = array( 'tmp' );
$writableDirectoriesFailure = array();
foreach( $writableDirectories as $directoryName )
{
    if( !is_writable( ROOT . DIRECTORY_SEPARATOR . $directoryName ) )
    {
        $writableDirectoriesFailure[] = $directoryName;
    }
}
// @todo check permissions in TV shows and movies folder (needed to downloads subtitles and nfo)

// DISPLAY ERRORS
if( !empty( $phpExtensionsFailure ) )
{
    echo "Missing PHP extensions : " . implode( ", ", $phpExtensionsFailure ) . "<br />";
    echo "First check if you have enabled these extensions in your php.ini file or any other included files : 
<pre>
" . implode( "\n", explode( ", ", php_ini_scanned_files() ) ) . "
</pre>";
    echo "If needed, you can try to do this :
<pre>
aptitude install sqlite
</pre>
This should install both sqlite and pdo_sqlite";
    echo "<hr />";
}

if( !empty( $writableDirectoriesFailure ) )
{
    $apacheUser = exec('whoami');
    echo "These directories are not writable by your browser : " . implode( ", ", $writableDirectoriesFailure ) . "<br />";
    echo "Document root is : " . ROOT  . "<br />";
    echo "Apache user : {$apacheUser}<br />";
    echo "You can try to do this :
<pre>
cd " . ROOT . "
chgrp -R {$apacheUser} " . implode( " ", $writableDirectoriesFailure ) . "
chmod -R g+rw " . implode( " ", $writableDirectoriesFailure ) . "
</pre>";
    echo "<hr />";
}

if( !empty( $phpExtensionsFailure ) or !empty( $writableDirectoriesFailure ) )
{
    echo "<br />";
    echo "Please fix this and retry";
    exit(1);
}
