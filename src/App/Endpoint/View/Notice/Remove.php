<?php

namespace App\Endpoints\View\Notice;

use App\Template\Form as Form;

class Manage extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("html_title", " ~ Remove");
        $this->output->addSwapTagString("page_title", " Remove notice:" . $this->page);
        $this->output->setSwapTagString("page_actions", "");
        if (in_array($this->page, [6,10]) == true) {
            $this->output->redirect("notice?bubblemessage=This notice is protected&bubbletype=warning");
            return;
        }

        $form = new form();
        $form->target("notice/remove/" . $this->page . "");
        $form->required(true);
        $form->col(6);
        $form->group("Warning</h4><p>If the notice currenly in use this will fail</p><h4>");
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
