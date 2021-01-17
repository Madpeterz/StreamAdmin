<?php

namespace App\Endpoints\View\Search;

use App\Template\Form;

class Remove extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("html_title", " ~ Remove");
        $this->output->addSwapTagString("page_title", " Remove server:" . $this->page);
        $this->setSwapTag("page_actions", "");

        $form = new Form();
        $form->target("server/remove/" . $this->page . "");
        $form->required(true);
        $form->col(6);
        $form->group("Warning</h4><p>If the server currenly in use this will fail</p><h4>");
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
