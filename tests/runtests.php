<?php
// require_once 'config.php';

ini_set( 'include_path', "/usr/share/php/ezc:/usr/share/php:." );

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';
require_once 'tests/lib/mkvmerge/command_generator_test.php';

if ( !defined('PHPUnit_MAIN_METHOD' ) )
{
    define('PHPUnit_MAIN_METHOD', 'AllTests::main' );
}

class AllTests
{
    public static function main()
    {

        $suite = new PHPUnit_Framework_TestSuite( 'AllTests' );
        $suite->addTestSuite( 'MKVMergeCommandGeneratorTest' );
        $result = PHPUnit_TextUI_TestRunner::run( $suite );
    }
}

if ( PHPUnit_MAIN_METHOD == 'AllTests::main')
{
    AllTests::main();
}
?>
