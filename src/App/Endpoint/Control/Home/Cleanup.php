<?php

namespace App\Endpoint\Control\Home;

use App\Framework\ViewAjax;

class Cleanup extends ViewAjax
{
    protected $deleleted_entrys = 0;
    public function process(): void
    {
        if ($this->siteConfig->getSession()->getOwnerLevel() != true) {
            $this->failed("Only the system owner can access this area");
            $this->setSwapTag("redirect", "");
            return;
        }
        if (defined("UNITTEST") == true) {
            $this->failed("Unable to run cleanup code (it would delete unit tests...)");
            return;
        }
        $this->delTree('fake');
        $this->delTree(DEEPFOLDERPATH . '/tests');
        $this->setSwapTag("redirect", "home");
        $this->ok("Deleted " . $this->deleleted_entrys . " entrys");
    }

    protected function delTree($dir): bool
    {
        $files = array_diff(scandir($dir), ['.','..']);
        foreach ($files as $file) {
            if (is_dir("$dir/$file") == true) {
                $this->delTree("$dir/$file");
                continue;
            }
            unlink("$dir/$file");
            $this->deleleted_entrys++;
        }
        $this->deleleted_entrys++;
        return rmdir($dir);
    }
}
