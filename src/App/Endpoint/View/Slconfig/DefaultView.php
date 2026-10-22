<?php

namespace App\Endpoint\View\Slconfig;

use App\Models\Avatar;
use YAPF\Bootstrap\Template\Form;
use App\Models\Set\TimezonesSet;

class DefaultView extends View
{
    protected function reissueNeeded(?string $input, int $minlen, int $maxlen): ?string
    {
        if ($input === null) {
            return null;
        }
        if (nullSafeStrLen($input) < $minlen) {
            return "! Needs Reissue !";
        } elseif (nullSafeStrLen($input) > $maxlen) {
            return "! Needs Reissue !";
        }
        return $input;
    }
    public function process(): void
    {
        $avatar = new Avatar();
        $avatar->loadID($this->siteConfig->getSlConfig()->_OwnerAvatarLink);
        $slConfigObj = $this->siteConfig->getSlConfig();
        $slConfigObj->_PublicLinkCode = $this->reissueNeeded($slConfigObj->_PublicLinkCode, 6, 12);
        $slConfigObj->_HttpInboundSecret = $this->reissueNeeded($slConfigObj->_HttpInboundSecret, 6, 12);
        $slConfigObj->_HudLinkCode = $this->reissueNeeded($slConfigObj->_HudLinkCode, 6, 12);
        $slConfigObj->_SlLinkCode = $this->reissueNeeded($slConfigObj->_SlLinkCode, 6, 10);

        $timezones_set = new TimezonesSet();
        $timezones_set->loadAll();

        $form = new Form();
        $form->target("slconfig/update/" . $this->siteConfig->getPage() . "");
        $form->required(true);
        $form->col(6);
        $form->group("Core");
        $form->directAdd("Current owner: " . $avatar->_AvatarName . "<br/>");
        $form->textInput(
            "owneravuid",
            "Owner avatar UID <a data-toggle=\"modal\" data-target=\"#AvatarPicker\" "
                . "href=\"#\" target=\"_blank\">Find</a>",
            8,
            $avatar->_AvatarUid,
            "Not a SL uuid!"
        );
        $form->select(
            "ui_tweaks_clients_fulllist",
            "Clients [Full list]",
            $slConfigObj->_ClientsListMode,
            $this->disableEnable
        );
        $form->textInput(
            "ui_tweaks_datatableItemsPerPage",
            "Datatables items per page",
            3,
            $slConfigObj->_DatatableItemsPerPage,
            "10 to 200"
        );
        $form->select(
            "ui_tweaks_clientsShowServer",
            "Show server on clients list",
            $slConfigObj->_ClientsDisplayServer,
            $this->yesNo
        );
        $form->select(
            "ui_tweaks_groupStreamsBy",
            "Group streams by",
            $slConfigObj->_StreamListOption,
            [0 => "Package", 1 => "Server"]
        );
        $form->col(6);
        $form->group("Resellers");
        $form->directAdd("<br/>");
        $form->select("newResellers", "Auto accept resellers", $slConfigObj->_NewResellers, $this->yesNo);
        $form->textInput(
            "newResellersRate",
            "Auto accepted resellers rate (As a %)",
            36,
            $slConfigObj->_NewResellersRate,
            "1 to 100"
        );
        $form->col(6);
        $form->directAdd("<br/>");
        $form->group("Misc settings");
        $form->select(
            "displayTimezoneLink",
            "Default timezone",
            $slConfigObj->_DisplayTimezoneLink,
            $timezones_set->getLinkedArray("id", "name")
        );
        $form->col(6);
        $form->directAdd("<br/>");
        $form->group("Access keys");
        $form->textInput(
            "slLinkCode",
            "Venders & Servers",
            30,
            $slConfigObj->_SlLinkCode,
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
            $slConfigObj->_HudLinkCode,
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
            $slConfigObj->_HudAllowDiscord,
            $this->yesNo
        );
        $form->textInput(
            "hudDiscordLink",
            "Discord join link",
            128,
            $slConfigObj->_HudDiscordLink,
            "Discord invite URL"
        );
        $form->select(
            "hudAllowGroup",
            "Show group link",
            $slConfigObj->_HudAllowGroup,
            $this->yesNo
        );
        $form->textInput(
            "hudGroupLink",
            "SL group url",
            128,
            $slConfigObj->_HudGroupLink,
            "SL grouplink URL"
        );
        $form->col(6);
        $form->directAdd("<br/>");
        $form->group("-");
        $form->select(
            "hudAllowDetails",
            "Allow details requests",
            $slConfigObj->_HudAllowDetails,
            $this->yesNo
        );
        $form->select(
            "hudAllowRenewal",
            "Allow renewals (Requires Allow details)",
            $slConfigObj->_HudAllowRenewal,
            $this->yesNo
        );
        $form->col(6);
        $form->directAdd("<br/>");
        $form->group("Events API <a target=\"_BLANK\" href=\"
        https://github.com/Madpeterz/StreamAdmin/wiki/Events-API\">?</a>");
        $form->select("eventsAPI", "", $slConfigObj->_EventsAPI, $this->disableEnable);
        if ($this->siteConfig->getSession()->getOwnerLevel() == true) {
            $form->directAdd("<br/>");
            $form->group("Marketplace");
            $form->select("enableCoupons", "Enable coupons", $slConfigObj->_EnableCoupons, $this->yesNo);
            $form->textInput(
                "ansSalt",
                "ANS salt <a target=\"_BLANK\" href=\"
            https://marketplace.secondlife.com/en-US/merchants/98052/store/automated_notification\">?</a>",
                50,
                $slConfigObj->_AnsSalt,
                "ANS salt"
            );
        }
        $form->col(6);
        $form->directAdd("<br/>");
        $form->group("Global limits");
        $form->select("limitStreams", "Enforce total streams", $slConfigObj->_LimitStreams, $this->yesNo);
        $form->select("limitTime", "Enforce rental time limit", $slConfigObj->_LimitTime, $this->yesNo);
        $form->numberInput(
            "maxStreamTimeDays",
            "Max rental in days (7 to 365)",
            $slConfigObj->_MaxStreamTimeDays,
            365,
            "Max days"
        );
        $form->numberInput(
            "maxTotalStreams",
            "Max total streams (1 to 2000)",
            $slConfigObj->_MaxTotalStreams,
            2000,
            "Max number of streams per client"
        );
        $this->setSwapTag("page_content", $form->render("Update", "primary"));

        $this->setSwapTag(
            "page_actions",
            "<a href='[[SITE_URL]]slconfig/reissue'><button type='button' class='btn btn-danger'>Reissue</button></a>"
        );
    }
}
