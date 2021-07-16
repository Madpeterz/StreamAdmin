<?php

namespace App\Endpoint\View\Home;

use App\Template\Form;
use App\Template\Grid;

class DefaultView extends HomeDisplayData
{
    public function process(): void
    {
        $this->main_grid = new Grid();
        if ($this->session->getOwnerLevel() == 1) {
            $this->unsafeWorkspace();
        }
        $this->loadDatasets();
        $this->displayDatasets();
        $this->output->addSwapTagString("page_content", $this->main_grid->getOutput());
    }

    protected function unsafeWorkspace(): void
    {
        $need_cleanup = false;
        $why_unsafe = "";
        if (is_dir('fake') == true) {
            $need_cleanup = true;
            $why_unsafe = "faker public folder found";
        }
        if (is_dir(DEEPFOLDERPATH . '/tests') == true) {
            $need_cleanup = true;
            if ($why_unsafe != "") {
                $why_unsafe .= " , ";
            }
            $why_unsafe .= " tests folder found ";
        }
        if ($need_cleanup == true) {
            $form = new Form();
            $form->mode("post");
            $form->target("home/cleanup");
            $formcode = $form->render("Cleanup", "danger");
            $this->main_grid->addContent('<div class="jumbotron">
            <h1 class="display-4">Secure install</h1>
            <p class="lead">' . $why_unsafe . '</p>
            <hr class="my-4">
            <p>Please run the cleanup tool now!</p>
            <p class="lead">
                ' . $formcode . '
            </p>
          </div>', 12);
        }
    }
}
