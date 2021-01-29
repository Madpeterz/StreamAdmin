<?php

namespace App\Endpoint\View\Stream;

use App\Models\Package;

class Inpackage extends Withstatus
{
    public function process(): void
    {
        $this->output->addSwapTagString("page_title", " In package: ");
        $package = new Package();
        if ($package->loadByField("packageUid", $this->page) == false) {
            $this->output->redirect("stream?messagebubble=Unable to find package&bubbletype=warning");
            return;
        }
        $this->output->addSwapTagString("page_title", $package->getName());
        $this->whereconfig = [
            "fields" => ["packageLink"],
            "values" => [$package->getId()],
            "types" => ["i"],
            "matches" => ["="],
        ];
        parent::process();
    }
}
