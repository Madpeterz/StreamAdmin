<?php

namespace App\Endpoint\View\Client;

use App\Template\Form;

class Revoke extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("html_title", " ~ Revoke");
        $this->output->addSwapTagString("page_title", "revoke client rental:" . $this->page);
        $this->setSwapTag("page_actions", "");

        $form = new form();
        $form->target("client/revoke/" . $this->page . "");
        $form->required(true);
        $form->col(6);
        $form->group("Warning</h4><p>This will end the rental without any refund!</p><h4>");
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
        $this->output->addSwapTagString("page_content", $form->render("Revoke", "danger"));
    }
}
