<?php

namespace App\Endpoint\View\Slconfig;

use App\R7\Model\Avatar;
use App\Template\Form;
use App\R7\Set\TimezonesSet;

class DefaultView extends View
{
    public function process(): void
    {
        $avatar = new Avatar();
        $avatar->loadID($this->slconfig->getOwnerAvatarLink());
        $timezones_set = new TimezonesSet();
        $timezones_set->loadAll();

        $form = new Form();
        $form->target("slconfig/update/" . $this->page . "");
        $form->required(true);
        $form->col(6);
            $form->group("Core");
            $form->directAdd("Current owner: " . $avatar->getAvatarName() . "<br/>");
            $form->textInput(
                "owneravuid",
                "Owner avatar UID <a data-toggle=\"modal\" data-target=\"#AvatarPicker\" "
                . "href=\"#\" target=\"_blank\">Find</a>",
                8,
                $avatar->getAvatarUid(),
                "Not a SL uuid!"
            );
            $form->textInput(
                "slLinkCode",
                "Link code [SL->Server]",
                30,
                $this->slconfig->getSlLinkCode(),
                "The code shared by your vendors to connet"
            );
            $form->textInput(
                "publicLinkCode",
                "Public Link code [SL->Server]",
                30,
                $this->slconfig->getPublicLinkCode(),
                "The code shared by your user hud"
            );
            $form->textInput(
                "httpcode",
                "HTTP code [Apps->Server]",
                36,
                $this->slconfig->getHttpInboundSecret(),
                "Enter here"
            );
        if ($this->session->getOwnerLevel() == 1) {
            $form->col(6);
                $form->group("SMTP [Email sending support]");
                $form->textInput("smtpFrom", "From", 30, $this->slconfig->getSmtpFrom(), "From email address");
                $form->textInput(
                    "smtp_reply",
                    "Reply",
                    30,
                    $this->slconfig->getSmtpReplyTo(),
                    "Reply to email address"
                );
                $form->textInput("smtpHost", "Host", 30, $this->slconfig->getSmtpHost(), "SMTP host");
                $form->textInput("smtp_user", "Username", 30, "skip", "SMTP username (leave as skip to not update)");
                $form->textInput(
                    "smtp_code",
                    "Access code",
                    30,
                    "skip",
                    "SMTP access code [or password] (leave as skip to not update)"
                );
                $form->textInput(
                    "smtpPort",
                    "Port",
                    30,
                    $this->slconfig->getSmtpPort(),
                    "port to connect to for SMTP"
                );
        }
        $form->col(6);
            $form->group("Resellers");
            $form->directAdd("<br/>");
            $form->select("newResellers", "Auto accept resellers", $this->slconfig->getNewResellers(), $this->yesNo);
            $form->textInput(
                "newResellersRate",
                "Auto accepted resellers rate (As a %)",
                36,
                $this->slconfig->getNewResellersRate(),
                "1 to 100"
            );
        $form->col(6);
            $form->directAdd("<br/>");
            $form->group("Feature packs");
            $form->select("event_storage", "Event storage", $this->slconfig->getEventStorage(), $this->disableEnable);
        $form->col(6);
            $form->directAdd("<br/>");
            $form->group("Misc settings");
            $form->select(
                "ui_tweaks_clients_fulllist",
                "Clients [Full list]",
                $this->slconfig->getClientsListMode(),
                $this->disableEnable
            );
            $form->textInput(
                "ui_tweaks_datatableItemsPerPage",
                "Datatables items per page",
                3,
                $this->slconfig->getDatatableItemsPerPage(),
                "10 to 200"
            );
            $form->textInput(
                "apiDefaultEmail",
                "API default email",
                3,
                $this->slconfig->getApiDefaultEmail(),
                "Required to be a vaild email"
            );
            $form->select(
                "displayTimezoneLink",
                "Default timezone",
                $this->slconfig->getDisplayTimezoneLink(),
                $timezones_set->getLinkedArray("id", "name")
            );
        $this->setSwapTag("page_content", $form->render("Update", "primary"));
    }
}
