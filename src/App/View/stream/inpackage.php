<?php

namespace App\View\Stream;

use App\Models\Package;

class Inpackage extends Withstatus
{
    public function process(): void
    {
        $this->output->addSwapTagString("page_title", " In package:");
        $package = new Package();
        if ($package->loadByField("package_uid", $this->page) == false) {
            $this->output->redirect("stream?messagebubble=Unable to find package&bubbletype=warning");
            return;
        }
        $whereconfig = [
            "fields" => ["packagelink"],
            "values" => [$package->getId()],
            "types" => ["i"],
            "matches" => ["="],
        ];
        parent::process();
    }
}
