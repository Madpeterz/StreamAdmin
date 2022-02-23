<?php

namespace App\Endpoint\View\Staff;

use App\Models\Staff;
use YAPF\Bootstrap\Template\Form;

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
        data-actiontitle='Remove staff member " . $this->siteConfig->getPage() . "' 
        data-actiontext='Remove staff member' 
        data-actionmessage='This will fail is the staff is owner level' 
        data-targetendpoint='[[SITE_URL]]Staff/Remove/" . $this->siteConfig->getPage() . "' 
        class='btn btn-danger confirmDialog'>Remove</button></a>");
        $staff = new Staff();
        if ($staff->loadByField("id", $this->siteConfig->getPage()) == false) {
            $this->output->redirect("staff?bubblemessage=unable to find staff member&bubbletype=warning");
            return;
        }
        if ($this->slconfig->getOwnerAvatarLink() == $staff->getAvatarLink()) {
            $this->setSwapTag("page_actions", "");
        }

        $form = new Form();
        $form->target("staff/update/" . $this->siteConfig->getPage() . "");
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
