<?php

namespace App\Endpoints\View\Tree;

use App\Models\Package;
use App\Template\Form;
use App\Models\Treevender;
use App\Models\Treevenderpackages;

class Removepackage extends View
{
    public function process(): void
    {
        $treevender_packages = new Treevenderpackages();
        if ($treevender_packages->loadID($this->page) == false) {
            $this->output->redirect("tree?bubblemessage=Unable to find linked treevender package&bubbletype=warning");
            return;
        }
        $treevender = new Treevender();
        if ($treevender->loadID($treevender_packages->getTreevenderlink()) == false) {
            $this->output->redirect("tree?bubblemessage=Unable to find treevender "
            . "thats linked to this package link&bubbletype=warning");
            return;
        }
        $package = new Package();
        if ($package->loadID($treevender_packages->getPackagelink()) == false) {
            $this->output->redirect("tree?bubblemessage=Unable to find package&bubbletype=warning");
            return;
        }
        $this->output->addSwapTagString("html_title", " ~ Remove");
        $this->output->addSwapTagString("page_title", " Remove linked package:"
        . $package->getName() . " from tree vender:" . $treevender->getName());
        $this->output->setSwapTagString("page_actions", "");

        $form = new Form();
        $form->target("tree/removepackage/" . $this->page . "");
        $form->required(true);
        $form->col(6);
        $form->group("Warning");
        $form->textInput("accept", "Type \"Accept\"", 30, "", "This will remove the link to the package");
        $this->output->setSwapTagString("page_content", $form->render("Remove", "danger"));
    }
}
