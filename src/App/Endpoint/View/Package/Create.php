<?php

namespace App\Endpoint\View\Package;

use App\Models\Set\NoticenotecardSet;
use App\Models\Set\ServertypesSet;
use YAPF\Bootstrap\Template\Form;
use App\Models\Set\TemplateSet;

class Create extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("html_title", " ~ Create");
        $this->output->addSwapTagString("page_title", " Create new package");
        $this->setSwapTag("page_actions", "");

        $template_set = new TemplateSet();
        $template_set->loadAll();
        $servertypes_set = new ServertypesSet();
        $servertypes_set->loadAll();
        $noticeNotecards = new NoticenotecardSet();
        $noticeNotecards->loadAll();

        $form = new Form();
        $form->target("package/create");
        $form->required(true);
        $form->col(6);
        $form->group("Basics");
        $form->textInput("name", "Name", 30, "", "Package name [60 chars]");
        $form->select("templateLink", "Template", 0, $template_set->getLinkedArray("id", "name"));
        $form->select("servertypeLink", "Server type", 1, $servertypes_set->getLinkedArray("id", "name"));
        $form->col(6);
        $form->group("Terms");
        $form->numberInput("cost", "Cost L$", null, 5, "Max L$ 99999");
        $form->numberInput("days", "Days per cost", null, 3, "Max 999 days");
        $form->numberInput("listeners", "Listeners", null, 3, "Max listeners 999");
        $form->numberInput("bitrate", "Bitrate", null, 3, "Max kbps 999");
        $form->split();
        $form->col(6);
        $form->group("Textures");
        $form->uuidInput("textureSoldout", "Sold out", "e39e011c-a45a-b4a6-942a-adc1ecee43f7", "UUID of texture");
        $form->uuidInput(
            "textureInstockSmall",
            "In stock [Small]",
            "d7dae774-fd0a-3f46-0b7f-e2ac57996b23",
            "UUID of texture"
        );
        $form->uuidInput(
            "textureInstockSelected",
            "In stock [Selected]",
            "6bbbddce-93bf-a15d-9346-ba320d2eda27",
            "UUID of texture"
        );
        $form->col(6);
        $form->group("Auto DJ");
        $form->select("autodj", "Enabled", false, [false => "No",true => "Yes"]);
        $form->numberInput("autodjSize", "Storage GB", null, 3, "Max GB storage 9999");
        $form->split();
        $form->col(6);
        $form->group("Options");
            $form->select("welcomeNotecardLink", "Welcome notecard", 1, $noticeNotecards->getLinkedArray("id", "name"));
            $form->select("setupNotecardLink", "Setup notecard", 1, $noticeNotecards->getLinkedArray("id", "name"));
            $form->select("enableGroupInvite", "Group Invite", true, $this->disableEnable);
        $this->setSwapTag("page_content", $form->render("Create", "primary"));
    }
}
