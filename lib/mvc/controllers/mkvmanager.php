<?php
class mmMkvManagerController extends ezcMvcController
{
    public function doDefault()
    {
        $res = new ezcMvcResult;
        $res->variables['test'] = 'test';
        return $res;
    }

    /**
     * Returns the lines list, with links to further details about each
     * @return ezcMvcResult
     */
    public function doLignes()
    {
        $result = new ezcMvcResult;

        $scrapperLignes = new tclScraperLignes();
        $result->variables['lignes'] = $scrapperLignes->get();
        $result->variables['tcl-url'] = $scrapperLigne->url;

        return $result;
    }

    public function doFatal()
    {
        $result = new ezcMvcResult;
        $result->variables['exception'] = $this->request->variables['exception'];
        return $result;
    }

    public function doTest()
    {
        echo "HEY";
        return new ezcMvcResult();
    }

    public function doMkvMerge()
    {
        // $res = new ezcMvcResult;
        // $res->variables['test'] = 'test';
        $result = new ezcMvcResult;
        $result->variables['targetDisks'] = mmMkvManagerDiskHelper::diskList();
        if ( isset( $_POST['WinCmd'] ) )
            $result->variables = array_merge( $result->variables, mmApp::doConvertWinCMD( $_POST['WinCmd'], $_POST['Target'] ) );
        return $result;
    }
}
?>
