<?php

namespace App\Endpoint\View\Login;

use App\Template\Form;

class Clear extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("html_title", " ~ Clear");
        $this->output->addSwapTagString("page_title", " : Clear");
        $this->setSwapTag("page_actions", "");

        $form = new Form();
        $form->target("objects/clear");
        $form->required(true);
        $form->col(6);
        $form->group("Clear all objects (DB only)");
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
        $this->setSwapTag("page_content", $form->render("Clear", "warning"));
    }
}
