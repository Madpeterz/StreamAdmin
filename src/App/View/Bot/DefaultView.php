<?php

namespace App\View\Banlist;

use App\Models\Avatar;
use App\Models\Botconfig;
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
        $avatar->loadID($botconfig->getAvatarlink());
        $this->output->setSwapTagString("html_title", "Bot setup");
        $this->output->setSwapTagString("page_title", "Editing bot " . $avatar->getAvatarname());
        $this->output->setSwapTagString("page_actions", "");
        $form = new form();
        $form->target("bot/update");
        $form->required(true);
        $form->col(6);
        $form->group("Basic");
        $form->textInput(
            "avataruid",
            "Avatar UID <a data-toggle=\"modal\" data-target=\"#AvatarPicker\" href=\"#\" target=\"_blank\">Find</a>",
            30,
            $avatar->getAvatar_uid(),
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
        $form->select("notecards", "Create notecards", $botconfig->getNotecards(), [false => "No",true => "Yes"]);
        $form->select("ims", "Send ims", $botconfig->getIms(), [false => "No",true => "Yes"]);
        $this->output->setSwapTagString("page_content", $form->render("Update", "primary"));
    }
}
