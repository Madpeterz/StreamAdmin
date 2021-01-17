<?php

namespace App\Endpoints\View\Package;

use App\Models\Package;
use App\Models\ServertypesSet;
use App\Template\Form;
use App\Models\TemplateSet;

class Manage extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("html_title", " ~ Manage");
        $this->output->addSwapTagString("page_title", " Editing package");
        $this->setSwapTag("page_actions", "<a href='[[url_base]]package/remove/" . $this->page
        . "'><button type='button' class='btn btn-danger'>Remove</button></a>");

        $template_set = new TemplateSet();
        $template_set->loadAll();
        $servertypes_set = new ServertypesSet();
        $servertypes_set->loadAll();

        $package = new Package();
        if ($package->loadByField("package_uid", $this->page) == false) {
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
                "templatelink",
                "Template",
                $package->getTemplatelink(),
                $template_set->getLinkedArray("id", "name")
            );
            $form->select(
                "servertypelink",
                "Server type",
                $package->getServertypelink(),
                $servertypes_set->getLinkedArray("id", "name")
            );
            $form->textInput("api_template", "API template", 50, $package->getApi_template(), "API template name");
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
                "texture_uuid_soldout",
                "Sold out",
                36,
                $package->getTexture_uuid_soldout(),
                "UUID of texture"
            );
            $form->textureInput(
                "texture_uuid_instock_small",
                "In stock [Small]",
                36,
                $package->getTexture_uuid_instock_small(),
                "UUID of texture"
            );
            $form->textureInput(
                "texture_uuid_instock_selected",
                "In stock [Selected]",
                36,
                $package->getTexture_uuid_instock_selected(),
                "UUID of texture"
            );
        $form->col(6);
            $form->group("Auto DJ");
            $form->select("autodj", "Enabled", $package->getAutodj(), [false => "No",true => "Yes"]);
            $form->numberInput("autodj_size", "Storage GB", $package->getAutodj_size(), 3, "Max GB storage 9999");
        $this->setSwapTag("page_content", $form->render("Update", "primary"));
    }
}
