<?php

namespace App\Endpoints\View\Staff;

use App\Template\Form;

class Remove extends View
{
    public function process(): void
    {
        if ($this->session->getOwnerLevel() == false) {
            $this->output->redirect("staff?bubblemessage=Owner level access needed&bubbletype=warning");
            return;
        }
        $this->output->addSwapTagString("html_title", " ~ Remove");
        $this->output->addSwapTagString("page_title", " Remove staff member:" . $this->page);
        $this->output->setSwapTagString("page_actions", "");

        $form = new Form();
        $form->target("staff/remove/" . $this->page . "");
        $form->required(true);
        $form->col(6);
        $form->group("Warning</h4><p>The web interface will not allow you to remove owner level accounts!</p><h4>");
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
        $this->output->setSwapTagString("page_content", $form->render("Remove", "danger"));
    }
}
