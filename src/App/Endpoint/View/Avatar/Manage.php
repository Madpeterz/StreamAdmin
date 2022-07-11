<?php

namespace App\Endpoint\View\Avatar;

use App\Endpoint\View\Transactions\DefaultView;
use App\Models\Avatar;
use YAPF\Bootstrap\Template\Form as Form;
use YAPF\Bootstrap\Template\Grid;

class Manage extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("html_title", "~ Manage");

        $this->setSwapTag("page_actions", ""
        . "<button type='button' 
        data-actiontitle='Remove avatar " . $this->siteConfig->getPage() . "' 
        data-actiontext='Remove avatar' 
        data-actionmessage='If this avatar is being used (by banlist/rental/ect) this will fail' 
        data-targetendpoint='[[SITE_URL]]Avatar/Remove/" . $this->siteConfig->getPage() . "' 
        class='btn btn-danger confirmDialog'>Remove</button></a>");

        $avatar = new Avatar();
        if ($avatar->loadByField("avatarUid", $this->siteConfig->getPage())->status == false) {
            $this->setSwapTag("page_content", "Avatar not found via page config");
            $this->output->redirect("avatar?bubblemessage=unable to find avatar&bubbletype=warning");
            return;
        }
        $this->output->addSwapTagString("page_title", " : " . $avatar->getAvatarName() . " 
        [" . $avatar->getAvatarUid() . "]");
        $form = new form();
        $form->target("avatar/update/" . $this->siteConfig->getPage() . "");
        $form->required(true);
        $form->col(6);
        $form->textInput(
            "avatarName",
            "Name",
            125,
            $avatar->getAvatarName(),
            "Madpeter Zond [You can leave out Resident]"
        );
        $form->textInput(
            "avatarUUID",
            "SL UUID",
            3,
            $avatar->getAvatarUUID(),
            "SecondLife UUID [found on their SL profile]"
        );

        $grid = new Grid();
        $grid->addContent($form->render("Update", "primary"), 12);
        $grid->addContent("<hr><h4>Transactions</h4>", 12);
        $grid->addContent($this->getTransactionsForAvatar($avatar), 12);
        $this->setSwapTag("page_content", $grid->getOutput());
    }

    protected function getTransactionsForAvatar(Avatar $av): string
    {
        $renderList = new DefaultView();
        $renderList->loadTransactionsFromAvatar($av);
        return $renderList->renderTransactionTable();
    }
}
