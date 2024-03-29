<?php

namespace App\Endpoint\View\Staff;

use App\R7\Model\Staff;
use App\Template\Form;

class Manage extends View
{
    public function process(): void
    {
        if ($this->session->getOwnerLevel() == false) {
            $this->output->redirect("staff?bubblemessage=Owner level access needed&bubbletype=warning");
            return;
        }
        $this->output->addSwapTagString("html_title", " ~ Manage");
        $this->output->addSwapTagString("page_title", ": Editing staff member");
        $this->setSwapTag("page_actions", ""
        . "<button type='button' 
        data-actiontitle='Remove staff member " . $this->page . "' 
        data-actiontext='Remove staff member' 
        data-actionmessage='This will fail is the staff is owner level' 
        data-targetendpoint='[[url_base]]Staff/Remove/" . $this->page . "' 
        class='btn btn-danger confirmDialog'>Remove</button></a>");
        $staff = new Staff();
        if ($staff->loadByField("id", $this->page) == false) {
            $this->output->redirect("staff?bubblemessage=unable to find staff member&bubbletype=warning");
            return;
        }
        if ($this->slconfig->getOwnerAvatarLink() == $staff->getAvatarLink()) {
            $this->setSwapTag("page_actions", "");
        }

        $form = new Form();
        $form->target("staff/update/" . $this->page . "");
        $form->required(true);
        $form->col(6);
        $form->textInput(
            "username",
            "Username",
            40,
            $staff->getUsername(),
            "Used to login [does not have to be the same as their SL name]"
        );
        $this->setSwapTag("page_content", $form->render("Update", "primary"));
    }
}
