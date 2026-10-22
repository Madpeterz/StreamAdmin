<?php

namespace App\Endpoint\View\Stream;

use App\Models\Package;

class Inpackage extends Withstatus
{
    public function process(bool $usePackageNotServer = false): void
    {
        $this->output->addSwapTagString("page_title", " In package: ");
        $package = new Package();
        if ($package->loadByPackageUid($this->siteConfig->getPage())->status == false) {
            $this->output->redirect("stream?messagebubble=Unable to find package&bubbletype=warning");
            return;
        }
        $this->output->addSwapTagString("page_title", $package->_Name);
        $this->whereconfig = [
            "fields" => ["packageLink"],
            "values" => [$package->_Id],
            "types" => ["i"],
            "matches" => ["="],
        ];
        parent::process(false);
    }
}
