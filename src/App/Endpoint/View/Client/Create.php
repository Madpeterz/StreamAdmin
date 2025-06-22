<?php

namespace App\Endpoint\View\Client;

use App\Models\Set\PackageSet;
use App\Models\Set\ServerSet;
use YAPF\Bootstrap\Template\Form;

class Create extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("html_title", " ~ Create");
        $this->output->addSwapTagString("page_title", "Create new client");
        $this->setSwapTag("page_actions", "");
        $package_set = new PackageSet();
        $package_set->loadAll();
        $server_set = new ServerSet();
        $server_set->loadAll();
        $form = new Form();
        $form->target("client/create");
        $form->required(true);
        $form->col(6);
            $form->group("Basics");
            $form->textInput(
                "avataruid",
                "Avatar UID (Or UUID/Full name)",
                0,
                "",
                "Avatar uid | Madpeter Zond | xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
            );
            $form->directAdd("<a data-toggle=\"modal\" data-target=\"#AvatarPicker\" "
            . "href=\"#\" target=\"_blank\">Find avatar</a><br/>");
            $form->numberInput("daysremaining", "Days remaining", 0, 3, "Max 999");
            $form->textInput("streamuid", "Stream UID (Or port)", 4, "", "Stream uid | Port number");
        $form->col(6);
        $form->col(6);
            $form->directAdd("<br/>If there are multiple streams with the same port number you must use the UID!");
        $this->setSwapTag("page_content", $form->render("Create", "primary"));
    }
}
