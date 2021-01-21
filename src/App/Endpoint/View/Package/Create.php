<?php

namespace App\Endpoint\View\Package;

use App\Models\ServertypesSet;
use App\Template\Form;
use App\Models\TemplateSet;

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

        $form = new Form();
        $form->target("package/create");
        $form->required(true);
        $form->col(6);
        $form->group("Basics");
        $form->textInput("name", "Name", 30, "", "Package name [60 chars]");
        $form->select("templateLink", "Template", 0, $template_set->getLinkedArray("id", "name"));
        $form->select("servertypeLink", "Server type", 1, $servertypes_set->getLinkedArray("id", "name"));
        $form->textInput("apiTemplate", "API template", 50, "", "API template name");
        $form->col(6);
        $form->group("Terms");
        $form->numberInput("cost", "Cost L$", null, 5, "Max L$ 99999");
        $form->numberInput("days", "Days per cost", null, 3, "Max 999 days");
        $form->numberInput("listeners", "Listeners", null, 3, "Max listeners 999");
        $form->numberInput("bitrate", "Bitrate", null, 3, "Max kbps 999");
        $form->split();
        $form->col(6);
        $form->group("Textures");
        $form->uuidInput("textureSoldout", "Sold out", "", "UUID of texture");
        $form->uuidInput("textureInstockSmall", "In stock [Small]", "", "UUID of texture");
        $form->uuidInput("textureInstockSelected", "In stock [Selected]", "", "UUID of texture");
        $form->col(6);
        $form->group("Auto DJ");
        $form->select("autodj", "Enabled", false, [false => "No",true => "Yes"]);
        $form->numberInput("autodjSize", "Storage GB", null, 3, "Max GB storage 9999");
        $this->setSwapTag("page_content", $form->render("Create", "primary"));
    }
}
