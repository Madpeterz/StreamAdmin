<?php

namespace App\Endpoint\View\Package;

use App\Models\Package;
use App\Models\Set\NoticenotecardSet;
use App\Models\Set\ServertypesSet;
use YAPF\Bootstrap\Template\Form;
use App\Models\Set\TemplateSet;

class Manage extends View
{
    public function process(): void
    {
        $noticeNotecards = new NoticenotecardSet();
        $noticeNotecards->loadAll();
        $this->output->addSwapTagString("html_title", " ~ Manage");
        $this->output->addSwapTagString("page_title", " Editing package");
        $this->setSwapTag("page_actions", ""
            . "<button type='button' 
        data-actiontitle='Remove package " . $this->siteConfig->getPage() . "' 
        data-actiontext='Remove package' 
        data-actionmessage='This will fail if the package is in use!' 
        data-targetendpoint='[[SITE_URL]]Package/Remove/" . $this->siteConfig->getPage() . "' 
        class='btn btn-danger confirmDialog'>Remove</button></a>");

        $template_set = new TemplateSet();
        $template_set->loadAll();
        $servertypes_set = new ServertypesSet();
        $servertypes_set->loadAll();

        $package = new Package();
        if ($package->loadByPackageUid($this->siteConfig->getPage()) == false) {
            $this->output->redirect("package?bubblemessage=unable to find package&bubbletype=warning");
            return;
        }
        $this->output->addSwapTagString("page_title", ":" . $package->_Name);
        $form = new Form();
        $form->target("package/update/" . $this->siteConfig->getPage() . "");
        $form->required(true);
        $form->col(6);
        $form->group("Basics");
        $form->textInput("name", "Name", 30, $package->_Name, "Package name [60 chars]");
        $form->select(
            "templateLink",
            "Template",
            $package->_TemplateLink,
            $template_set->getLinkedArray("id", "name")
        );
        $form->select(
            "servertypeLink",
            "Server type",
            $package->_ServertypeLink,
            $servertypes_set->getLinkedArray("id", "name")
        );
        $form->col(6);
        $form->group("Terms");
        $form->numberInput("cost", "Cost L$", $package->_Cost, 5, "Max L$ 99999");
        $form->numberInput("days", "Days per cost", $package->_Days, 3, "Max 999 days");
        $form->numberInput("listeners", "Listeners", $package->_Listeners, 3, "Max listeners 999");
        $form->numberInput("bitrate", "Bitrate", $package->_Bitrate, 3, "Max kbps 999");
        $form->split();
        $form->col(6);
        $form->group("Textures");
        $form->textureInput(
            "textureSoldout",
            "Sold out",
            36,
            $package->_TextureSoldout,
            "UUID of texture"
        );
        $form->textureInput(
            "textureInstockSmall",
            "In stock [Small]",
            36,
            $package->_TextureInstockSmall,
            "UUID of texture"
        );
        $form->textureInput(
            "textureInstockSelected",
            "In stock [Selected]",
            36,
            $package->_TextureInstockSelected,
            "UUID of texture"
        );
        $form->col(6);
        $form->group("Auto DJ");
        $form->select("autodj", "Enabled", $package->_Autodj, [false => "No", true => "Yes"]);
        $form->numberInput("autodjSize", "Storage GB", $package->_AutodjSize, 3, "Max GB storage 9999");
        $form->split();
        $form->col(6);
        $form->group("Ext");
        $form->select(
            "welcomeNotecardLink",
            "Welcome notecard",
            $package->_WelcomeNotecardLink,
            $noticeNotecards->getLinkedArray("id", "name")
        );
        $form->select(
            "setupNotecardLink",
            "Setup notecard",
            $package->_SetupNotecardLink,
            $noticeNotecards->getLinkedArray("id", "name")
        );
        $form->select("enableGroupInvite", "Group Invite", $package->_EnableGroupInvite, $this->disableEnable);
        $form->col(6);
        $form->group("Limits [same package]");
        $form->select(
            "enforceCustomMaxStreams",
            "Enforce custom limit",
            $package->_EnforceCustomMaxStreams,
            $this->yesNo
        );
        $form->numberInput(
            "maxStreamsInPackage",
            "Max rentals",
            $package->_MaxStreamsInPackage,
            2000,
            "Max rentals in package per customer"
        );
        $this->setSwapTag("page_content", $form->render("Update", "primary"));
    }
}
