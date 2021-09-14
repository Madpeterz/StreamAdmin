<?php

namespace App\Endpoint\View\Slconfig;

use App\R7\Model\Avatar;
use App\Template\Form;
use App\R7\Set\TimezonesSet;

class DefaultView extends View
{
    protected function reissueNeeded(?string $input, int $minlen, int $maxlen): ?string
    {
        if ($input === null) {
            return null;
        }
        if (strlen($input) < $minlen) {
            return "! Needs Reissue !";
        } elseif (strlen($input) > $maxlen) {
            return "! Needs Reissue !";
        }
        return $input;
    }
    public function process(): void
    {
        $avatar = new Avatar();
        $avatar->loadID($this->slconfig->getOwnerAvatarLink());
        $this->slconfig->setPublicLinkCode($this->reissueNeeded($this->slconfig->getPublicLinkCode(), 6, 12));
        $this->slconfig->setHttpInboundSecret($this->reissueNeeded($this->slconfig->getHttpInboundSecret(), 6, 12));
        $this->slconfig->setHudLinkCode($this->reissueNeeded($this->slconfig->getHudLinkCode(), 6, 12));
        $this->slconfig->setSlLinkCode($this->reissueNeeded($this->slconfig->getSlLinkCode(), 6, 10));

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
            $form->group("Misc settings");
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
        $form->col(6);
            $form->directAdd("<br/>");
            $form->group("Access keys");
            $form->textInput(
                "slLinkCode",
                "Venders & Servers",
                30,
                $this->slconfig->getSlLinkCode(),
                "The code shared by your vendors to connect",
                "",
                "text",
                true
            );
            /*
            $form->textInput(
                "publicLinkCode",
                "= Not in use =",
                30,
                $this->slconfig->getPublicLinkCode(),
                "Not used yet",
                "",
                "text",
                true
            );
            */
            $form->textInput(
                "hudLinkCode",
                "Renter hud",
                30,
                $this->slconfig->getHudLinkCode(),
                "Used to connect the hud",
                "",
                "text",
                true
            );
            /*
            $form->textInput(
                "httpcode",
                "Secondbot ect",
                36,
                $this->slconfig->getHttpInboundSecret(),
                "Enter here"
            );
            */
        $form->col(6);
            $form->directAdd("<br/>");
            $form->group("Renter hud");
            $form->select(
                "hudAllowDiscord",
                "Show discord link",
                $this->slconfig->getHudAllowDiscord(),
                $this->yesNo
            );
            $form->textInput(
                "hudDiscordLink",
                "Discord join link",
                128,
                $this->slconfig->getHudDiscordLink(),
                "Discord invite URL"
            );
            $form->select(
                "hudAllowGroup",
                "Show group link",
                $this->slconfig->getHudAllowGroup(),
                $this->yesNo
            );
            $form->textInput(
                "hudGroupLink",
                "SL group url",
                128,
                $this->slconfig->getHudGroupLink(),
                "SL grouplink URL"
            );
        $form->col(6);
        $form->directAdd("<br/>");
        $form->group("-");
            $form->select(
                "hudAllowDetails",
                "Allow details requests",
                $this->slconfig->getHudAllowDetails(),
                $this->yesNo
            );
            $form->select(
                "hudAllowRenewal",
                "Allow renewals (Requires Allow details)",
                $this->slconfig->getHudAllowRenewal(),
                $this->yesNo
            );
        $form->col(6);
        $form->directAdd("<br/>");
        $form->group("Events API <a target=\"_BLANK\" href=\"
        https://github.com/Madpeterz/StreamAdmin/wiki/Events-API\">?</a>");
        $form->select("eventsAPI", "", $this->slconfig->getEventsAPI(), $this->disableEnable);
        $this->setSwapTag("page_content", $form->render("Update", "primary"));

        $this->setSwapTag(
            "page_actions",
            "<a href='[[url_base]]slconfig/reissue'><button type='button' class='btn btn-danger'>Reissue</button></a>"
        );
    }
}
