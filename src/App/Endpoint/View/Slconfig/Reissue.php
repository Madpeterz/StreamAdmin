<?php

namespace App\Endpoint\View\Slconfig;

use App\Template\Form;

class Reissue extends View
{
    public function process(): void
    {
        $this->setSwapTag("html_title", " Reissuing keys");
        $this->setSwapTag("page_title", " Reissuing keys");
        $this->setSwapTag("page_actions", "");
        $form = new Form();
        $form->target("Slconfig/Reissue");
        $form->required(true);
        $form->col(6);
        $form->group("<h4>This will reissue all the sec keys!</h4><p> 
        all venders, Huds and custom scripts will need to be updated!</p><h4>");
        $action = '
<div class="btn-group btn-group-toggle" data-toggle="buttons">
  <label class="btn btn-outline-danger active">
    <input type="radio" value="Accept" name="accept" autocomplete="off" > Lets do it
  </label>
  <label class="btn btn-outline-secondary">
    <input type="radio" value="Nevermind" name="accept" autocomplete="off" checked> Nevermind
  </label>
</div>';
        $form->directAdd($action);
        $this->setSwapTag("page_content", $form->render("Reissue", "danger"));
    }
}
