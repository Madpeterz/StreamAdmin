<?php

namespace App\Endpoint\View\Slconfig;

use App\Models\Avatar;
use YAPF\Bootstrap\Template\Form;
use App\Models\Sets\TimezonesSet;

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
        $avatar->loadID($this->siteConfig->getSlConfig()->getOwnerAvatarLink());
        $this->siteConfig->getSlConfig()->setPublicLinkCode($this->reissueNeeded($this->siteConfig->getSlConfig()->getPublicLinkCode(), 6, 12));
        $this->siteConfig->getSlConfig()->setHttpInboundSecret($this->reissueNeeded($this->siteConfig->getSlConfig()->getHttpInboundSecret(), 6, 12));
        $this->siteConfig->getSlConfig()->setHudLinkCode($this->reissueNeeded($this->siteConfig->getSlConfig()->getHudLinkCode(), 6, 12));
        $this->siteConfig->getSlConfig()->setSlLinkCode($this->reissueNeeded($this->siteConfig->getSlConfig()->getSlLinkCode(), 6, 10));

        $timezones_set = new TimezonesSet();
        $timezones_set->loadAll();

        $form = new Form();
        $form->target("slconfig/update/" . $this->siteConfig->getPage() . "");
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
                $this->siteConfig->getSlConfig()->getClientsListMode(),
                $this->disableEnable
            );
            $form->textInput(
                "ui_tweaks_datatableItemsPerPage",
                "Datatables items per page",
                3,
                $this->siteConfig->getSlConfig()->getDatatableItemsPerPage(),
                "10 to 200"
            );
        $form->col(6);
            $form->group("Resellers");
            $form->directAdd("<br/>");
            $form->select("newResellers", "Auto accept resellers", $this->siteConfig->getSlConfig()->getNewResellers(), $this->yesNo);
            $form->textInput(
                "newResellersRate",
                "Auto accepted resellers rate (As a %)",
                36,
                $this->siteConfig->getSlConfig()->getNewResellersRate(),
                "1 to 100"
            );
        $form->col(6);
            $form->directAdd("<br/>");
            $form->group("Misc settings");
            $form->textInput(
                "apiDefaultEmail",
                "API default email",
                3,
                $this->siteConfig->getSlConfig()->getApiDefaultEmail(),
                "Required to be a vaild email"
            );
            $form->select(
                "displayTimezoneLink",
                "Default timezone",
                $this->siteConfig->getSlConfig()->getDisplayTimezoneLink(),
                $timezones_set->getLinkedArray("id", "name")
            );
        $form->col(6);
            $form->directAdd("<br/>");
            $form->group("Access keys");
            $form->textInput(
                "slLinkCode",
                "Venders & Servers",
                30,
                $this->siteConfig->getSlConfig()->getSlLinkCode(),
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
                $this->siteConfig->getSlConfig()->getPublicLinkCode(),
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
                $this->siteConfig->getSlConfig()->getHudLinkCode(),
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
                $this->siteConfig->getSlConfig()->getHttpInboundSecret(),
                "Enter here"
            );
            */
        $form->col(6);
            $form->directAdd("<br/>");
            $form->group("Renter hud");
            $form->select(
                "hudAllowDiscord",
                "Show discord link",
                $this->siteConfig->getSlConfig()->getHudAllowDiscord(),
                $this->yesNo
            );
            $form->textInput(
                "hudDiscordLink",
                "Discord join link",
                128,
                $this->siteConfig->getSlConfig()->getHudDiscordLink(),
                "Discord invite URL"
            );
            $form->select(
                "hudAllowGroup",
                "Show group link",
                $this->siteConfig->getSlConfig()->getHudAllowGroup(),
                $this->yesNo
            );
            $form->textInput(
                "hudGroupLink",
                "SL group url",
                128,
                $this->siteConfig->getSlConfig()->getHudGroupLink(),
                "SL grouplink URL"
            );
        $form->col(6);
        $form->directAdd("<br/>");
        $form->group("-");
            $form->select(
                "hudAllowDetails",
                "Allow details requests",
                $this->siteConfig->getSlConfig()->getHudAllowDetails(),
                $this->yesNo
            );
            $form->select(
                "hudAllowRenewal",
                "Allow renewals (Requires Allow details)",
                $this->siteConfig->getSlConfig()->getHudAllowRenewal(),
                $this->yesNo
            );
        $form->col(6);
        $form->directAdd("<br/>");
        $form->group("Events API <a target=\"_BLANK\" href=\"
        https://github.com/Madpeterz/StreamAdmin/wiki/Events-API\">?</a>");
        $form->select("eventsAPI", "", $this->siteConfig->getSlConfig()->getEventsAPI(), $this->disableEnable);
        $this->setSwapTag("page_content", $form->render("Update", "primary"));

        $this->setSwapTag(
            "page_actions",
            "<a href='[[SITE_URL]]slconfig/reissue'><button type='button' class='btn btn-danger'>Reissue</button></a>"
        );
    }
}
