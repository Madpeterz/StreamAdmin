<?php

namespace App\Endpoint\View\Bot;

use App\R7\Model\Avatar;
use App\R7\Model\Botconfig;
use App\Template\Form as Form;

class DefaultView extends View
{
    public function process(): void
    {
        if ($this->session->getOwnerLevel() == false) {
            $this->output->redirect("config?bubblemessage=Owner level access needed&bubbletype=warning");
        }
        $botconfig = new Botconfig();
        $botconfig->loadID(1);
        $avatar = new Avatar();
        $avatar->loadID($botconfig->getAvatarLink());
        $this->setSwapTag("html_title", "Bot setup");
        $this->setSwapTag("page_title", "Editing bot " . $avatar->getAvatarName());
        $this->setSwapTag("page_actions", "");
        $form = new form();
        $form->target("bot/update");
        $form->required(true);
        $form->col(6);
        $form->group("Basic");
        $form->textInput(
            "avataruid",
            "Avatar UID <a data-toggle=\"modal\" data-target=\"#AvatarPicker\" href=\"#\" target=\"_blank\">Find</a>",
            30,
            $avatar->getAvatarUid(),
            "Avatar uid [Not the same as a SL UUID!]"
        );
        $form->textInput(
            "secret",
            "Secret SL->Bot",
            36,
            $botconfig->getSecret(),
            "Bot secret [Found in ***.json or env value]"
        );
        $form->col(6);
        $form->group("Actions");
        $form->select("notecards", "Create notecards", $botconfig->getNotecards(), $this->yesNo);
        $form->select("ims", "Send ims", $botconfig->getIms(), $this->yesNo);
        $form->split();
        $form->col(6);
        $form->group("Auto inviter");
        $form->select("invites", "Send invites", $botconfig->getInvites(), $this->yesNo);
        $form->textInput(
            "inviteGroupUUID",
            "Group UUID",
            36,
            $botconfig->getInviteGroupUUID(),
            "Group UUID to invite to [with the everyone role]"
        );
        $form->directAdd("<br/> <p>You can disable group invites per package if needed!</p>");
        $this->setSwapTag("page_content", $form->render("Update", "primary"));
    }
}
