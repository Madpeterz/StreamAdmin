<?php

namespace App\Endpoint\View\Slconfig;

use App\R7\Model\Avatar;
use App\Template\Form;
use App\R7\Set\TimezonesSet;
use App\Template\Grid;

class DefaultView extends View
{
    public function process(): void
    {
        $avatar = new Avatar();
        $avatar->loadID($this->slconfig->getOwnerAvatarLink());
        if (strlen($this->slconfig->getPublicLinkCode()) < 6) {
            $this->slconfig->setPublicLinkCode("! Needs Reissue !");
        } elseif (strlen($this->slconfig->getPublicLinkCode()) > 12) {
            $this->slconfig->setPublicLinkCode("! Needs Reissue !");
        }
        if (strlen($this->slconfig->getHttpInboundSecret()) < 5) {
            $this->slconfig->setHttpInboundSecret("! Needs Reissue !");
        } elseif (strlen($this->slconfig->getHttpInboundSecret()) > 30) {
            $this->slconfig->setHttpInboundSecret("! Needs Reissue !");
        }

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
                "Secondlife code",
                30,
                $this->slconfig->getSlLinkCode(),
                "The code shared by your vendors to connet",
                "",
                "text",
                true
            );
            $form->textInput(
                "publicLinkCode",
                "Hud access code",
                30,
                $this->slconfig->getPublicLinkCode(),
                "The code shared by your user hud",
                "",
                "text",
                true
            );
            $form->textInput(
                "httpcode",
                "Apps access code",
                36,
                $this->slconfig->getHttpInboundSecret(),
                "Enter here",
                "",
                "text",
                true
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

        $this->setSwapTag("page_content", $form->render("Update", "primary", false, true));

        $this->setSwapTag(
            "page_actions",
            "<a href='[[url_base]]slconfig/reissue'><button type='button' class='btn btn-danger'>Reissue</button></a>"
        );
    }
}
