<?php

namespace App\Endpoint\View\Reseller;

use YAPF\Bootstrap\Template\Form;

class Remove extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("html_title", " ~ Remove");
        $this->output->addSwapTagString("page_title", "Remove reseller:" . $this->siteConfig->getPage());
        $this->setSwapTag("page_actions", "");
        $form = new Form();
        $form->target("reseller/remove/" . $this->siteConfig->getPage() . "");
        $form->required(true);
        $form->col(6);
        $form->group("Warning</h4><p>If the reseller is currenly in use this will fail"
        . "<br/>You should just mark them as not allowed!</p><h4>");
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
