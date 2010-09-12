<?php
class mmAjaxController extends ezcMvcController
{
    /**
     * Converts the windows MKVMerge command to a UNIX one and returns it
     */
    public function doMkvMerge()
    {
        $result = new ezcMvcResult;
        $result->variables['debug'] = print_r( $_POST, true );

        if ( !isset( $_POST['WinCmd'] ) )
        {
            $result->variables['error'] = "MissingTarget";
        }
        else
        {
            $result->variables['command'] = mmApp::doConvertWinCMD( $_POST['WinCmd'], $_POST['Target'] );
        }
        return $result;
    }

    /**
     * Based on a windows command, returns the disk where the target file fits best
     */
    public function doBestFit()
    {

    }
}
?>
