<?php

namespace App\Endpoint\View\Bot;

use App\Models\Avatar;
use App\Models\Botconfig;
use YAPF\Bootstrap\Template\Form;

class DefaultView extends View
{
    public function process(): void
    {
        if ($this->siteConfig->getSession()->getOwnerLevel() == false) {
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
            "Avatar UID <a data-toggle=\"modal\" data-target=\"#AvatarPicker\"" .
                " href=\"#\" target=\"_blank\">Find</a>",
            8,
            $avatar->getAvatarUid(),
            "Avatar uid [Not the same as a SL UUID!]"
        );
        $form->textInput(
            "secret",
            "Secret SL->Bot",
            12,
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
            0,
            $botconfig->getInviteGroupUUID(),
            "Group UUID to invite to [with the everyone role]"
        );
        $form->directAdd("<br/> <p>You can disable group invites per package if needed!</p>");
        $form->col(6);
        $form->group("HTTP mode");
        $form->select("httpMode", "Use HTTP", $botconfig->getHttpMode(), $this->disableEnable);
        $form->textInput("httpURL", "URL", 0, $botconfig->getHttpURL(), "HTTP url to the bot");
        $form->directAdd("<br/> <p>For help setting up HTTP mode with your bot please talk to Madpeter<br/> " .
            "HTTP mode works with a cronjob otherwise it is pointless to enable</p> <br/> 
            Notes: Bot support requires cron to be setup!");
        $this->setSwapTag("page_content", $form->render("Update", "primary"));
    }
}
