<?php

namespace App\Endpoint\Control\Home;

use App\Template\ViewAjax;

class Cleanup extends ViewAjax
{
    protected $deleleted_entrys = 0;
    public function process(): void
    {
        if ($this->session->getOwnerLevel() != 1) {
            $this->setSwapTag("message", "Only the system owner can access this area");
            $this->setSwapTag("redirect", "");
            return;
        }
        if (defined("UNITTEST") == true) {
            $this->setSwapTag("message", "Unable to run cleanup code (it would delete unit tests...)");
            return;
        }
        $this->delTree(ROOTFOLDER . '/App/public_html/fake');
        $this->delTree(ROOTFOLDER . 'tests');
        $this->setSwapTag("status", true);
        $this->setSwapTag("message", "Deleted " . $this->deleleted_entrys . " entrys");
        $this->setSwapTag("redirect", "home");
    }

    protected function delTree($dir): bool
    {
        $files = array_diff(scandir($dir), ['.','..']);
        foreach ($files as $file) {
            if (is_dir("$dir/$file") == true) {
                $this->delTree("$dir/$file");
            } else {
                unlink("$dir/$file");
                $this->deleleted_entrys++;
            }
        }
        $this->deleleted_entrys++;
        return rmdir($dir);
    }
}
