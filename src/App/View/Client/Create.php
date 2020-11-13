<?php

namespace App\View\Client;

use App\PackageSet;
use App\ServerSet;
use App\Template\Form;

class Create extends View
{
    public function process()
    {
        $this->output->addSwapTagString("html_title", " ~ Create");
        $this->output->addSwapTagString("page_title", "Create new client");
        $this->output->setSwapTagString("page_actions", "");
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
                30,
                "",
                "Avatar uid | Madpeter Zond | xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
            );
            $form->directAdd("<a data-toggle=\"modal\" data-target=\"#AvatarPicker\" "
            . "href=\"#\" target=\"_blank\">Find/Add avatar</a><br/>");
            $form->numberInput("daysremaining", "Days remaining", 0, 3, "Max 999");
            $form->textInput("streamuid", "Stream UID (Or port)", 30, "", "Stream uid | Port number");
        $form->col(6);
        $form->col(6);
            $form->directAdd("<br/>If there are multiple streams with the same port number you must use the UID!");
        $this->output->setSwapTagString("page_content", $form->render("Create", "primary"));
    }
}
