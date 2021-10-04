<?php

namespace App\Endpoint\View\Package;

use App\R7\Model\Package;
use App\R7\Set\NoticenotecardSet;
use App\R7\Set\ServertypesSet;
use App\Template\Form;
use App\R7\Set\TemplateSet;

class Manage extends View
{
    public function process(): void
    {
        $noticeNotecards = new NoticenotecardSet();
        $noticeNotecards->loadAll();
        $this->output->addSwapTagString("html_title", " ~ Manage");
        $this->output->addSwapTagString("page_title", " Editing package");
        $this->setSwapTag("page_actions", "<a href='[[url_base]]package/remove/" . $this->page
        . "'><button type='button' class='btn btn-danger'>Remove</button></a>");

        $template_set = new TemplateSet();
        $template_set->loadAll();
        $servertypes_set = new ServertypesSet();
        $servertypes_set->loadAll();

        $package = new Package();
        if ($package->loadByField("packageUid", $this->page) == false) {
            $this->output->redirect("package?bubblemessage=unable to find package&bubbletype=warning");
            return;
        }
        $this->output->addSwapTagString("page_title", ":" . $package->getName());
        $form = new Form();
        $form->target("package/update/" . $this->page . "");
        $form->required(true);
        $form->col(6);
            $form->group("Basics");
            $form->textInput("name", "Name", 30, $package->getName(), "Package name [60 chars]");
            $form->select(
                "templateLink",
                "Template",
                $package->getTemplateLink(),
                $template_set->getLinkedArray("id", "name")
            );
            $form->select(
                "servertypeLink",
                "Server type",
                $package->getServertypeLink(),
                $servertypes_set->getLinkedArray("id", "name")
            );
        $form->col(6);
            $form->group("Terms");
            $form->numberInput("cost", "Cost L$", $package->getCost(), 5, "Max L$ 99999");
            $form->numberInput("days", "Days per cost", $package->getDays(), 3, "Max 999 days");
            $form->numberInput("listeners", "Listeners", $package->getListeners(), 3, "Max listeners 999");
            $form->numberInput("bitrate", "Bitrate", $package->getBitrate(), 3, "Max kbps 999");
        $form->split();
        $form->col(6);
            $form->group("Textures");
            $form->textureInput(
                "textureSoldout",
                "Sold out",
                36,
                $package->getTextureSoldout(),
                "UUID of texture"
            );
            $form->textureInput(
                "textureInstockSmall",
                "In stock [Small]",
                36,
                $package->getTextureInstockSmall(),
                "UUID of texture"
            );
            $form->textureInput(
                "textureInstockSelected",
                "In stock [Selected]",
                36,
                $package->getTextureInstockSelected(),
                "UUID of texture"
            );
        $form->col(6);
            $form->group("Auto DJ");
            $form->select("autodj", "Enabled", $package->getAutodj(), [false => "No",true => "Yes"]);
            $form->numberInput("autodjSize", "Storage GB", $package->getAutodjSize(), 3, "Max GB storage 9999");
        $form->split();
        $form->col(6);
            $form->group("Ext");
            $form->textInput("apiTemplate", "API template", 50, $package->getApiTemplate(), "API template name");
            $form->select(
                "welcomeNotecardLink",
                "Welcome notecard",
                $package->getWelcomeNotecardLink(),
                $noticeNotecards->getLinkedArray("id", "name")
            );
            $form->select(
                "setupNotecardLink",
                "Setup notecard",
                $package->getSetupNotecardLink(),
                $noticeNotecards->getLinkedArray("id", "name")
            );
        $form->col(6);
            $form->group("Options");
            $form->select("enableGroupInvite", "Group Invite", $package->getEnableGroupInvite(), $this->disableEnable);
        $form->split();
            $form->col(6);
            $form->group("API");
            $form->select(
                "apiAllowAutoSuspend",
                "Allow auto suspend",
                $package->getApiAllowAutoSuspend(),
                $this->yesNo
            );
            $form->numberInput(
                "apiAutoSuspendDelayHours",
                "Auto suspend delay [in hours]",
                $package->getApiAutoSuspendDelayHours(),
                3,
                "set to zero to skip the delay"
            );
        $this->setSwapTag("page_content", $form->render("Update", "primary"));
    }
}
