<?php

namespace App\Endpoint\View\Tree;

use App\R7\Model\Package;
use App\Template\Form;
use App\R7\Model\Treevender;
use App\R7\Model\Treevenderpackages;

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
        if ($treevender->loadID($treevender_packages->getTreevenderLink()) == false) {
            $this->output->redirect("tree?bubblemessage=Unable to find treevender "
            . "thats linked to this package link&bubbletype=warning");
            return;
        }
        $package = new Package();
        if ($package->loadID($treevender_packages->getPackageLink()) == false) {
            $this->output->redirect("tree?bubblemessage=Unable to find package&bubbletype=warning");
            return;
        }
        $this->output->addSwapTagString("html_title", " ~ Remove");
        $this->output->addSwapTagString("page_title", "Remove package:" . $package->getName());
        $this->setSwapTag("page_actions", "");

        $form = new Form();
        $form->target("tree/removepackage/" . $this->page . "");
        $form->required(true);
        $form->col(6);
        $form->group("Warning");
        $action = '
    <div class="btn-group btn-group-toggle" data-toggle="buttons">
      <label class="btn btn-outline-danger active">
        <input type="radio" value="Accept" name="accept" autocomplete="off" > Accept
      </label>
      <label class="btn btn-outline-secondary">
        <input type="radio" value="Nevermind" name="accept" autocomplete="off" checked> Nevermind
      </label>
    </div>';
        $form->directAdd($action);
        $this->setSwapTag("page_content", $form->render("Remove", "danger"));
    }
}
