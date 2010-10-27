<?php
/**
* Ideally, this would test the best fit behaviour, based on a structure
* created in the setUp method. The issue is that files won't have a real size,
* and it would be required to fake the size somehow... not obvious :(
*
* The size would have to be provided by an external method, maybe an object
* of some sorts... that is a LOT of work for a test, but it might deserve it:
*
* - BIG episode, 1st episode of the show, enough space on the disk where we left off
* - BIG episode, NOT 1st episode of the show, enough space on the disk where we left off
* - BIG episode, 1st episode of the show, NOT enough space on the disk where we left off
* - BIG episode, NOT 1st episode of the show, NOT enough space on the disk where we left off
* - SMALL episode, 1st episode of the show, enough space on the disk where we left off
* - SMALL episode, NOT 1st episode of the show, enough space on the disk where we left off
* - SMALL episode, 1st episode of the show, NOT enough space on the disk where we left off
* - SMALL episode, NOT 1st episode of the show, NOT enough space on the disk where we left off
*/
class lib_mkvmergeDiskHelperTest extends PHPUnit_Framework_TestCase
{
    public function testBestTVEpisodeFit()
    {

    }
}
?>