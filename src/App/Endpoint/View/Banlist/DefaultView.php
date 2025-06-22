<?php

namespace App\Endpoint\View\Banlist;

use App\Models\Set\AvatarSet;
use App\Models\Set\BanlistSet;
use YAPF\Bootstrap\Template\Form as Form;
use YAPF\Bootstrap\Template\Grid;

class DefaultView extends View
{
    public function process(): void
    {
        if ($this->siteConfig->getSession()->getOwnerLevel() != 1) {
            $this->output->redirect("?bubblemessage=sorry owner only&bubbletype=warning");
            return;
        }
        $match_with = "newest";

        $name = $this->input->get("name")->asString();
        $uuid = $this->input->get("uuid")->asString();
        $wherefields = [];
        $wherevalues = [];
        $wheretypes = [];
        $wherematchs = [];

        if (nullSafeStrLen($uuid) == 36) {
            $match_with = "uuid";
            $wherefields = ["avatarUUID"];
            $wherevalues = [$uuid];
            $wheretypes = ["s"];
            $wherematchs = ["="];
        } elseif (nullSafeStrLen($name) >= 2) {
            $match_with = "name";
            $wherefields = ["avatarName"];
            $wherevalues = [$name];
            $wheretypes = ["s"];
            $wherematchs = ["% LIKE %"];
        }
        $banlist_set = new BanlistSet();
        $avatar_set = new AvatarSet();
        if ($match_with == "newest") {
            $banlist_set->loadNewest(30);
            $avatar_set = $banlist_set->relatedAvatar();
            $this->output->addSwapTagString("page_title", " Newest 30 avatars banned");
        } else {
            $where_config = [
                "fields" => $wherefields,
                "values" => $wherevalues,
                "types" => $wheretypes,
                "matches" => $wherematchs,
            ];
            $avatar_set->loadWithConfig($where_config);
            if ($match_with == "name") {
                $this->output->addSwapTagString("page_title", "Names containing: " . $name);
            } else {
                $this->output->addSwapTagString("page_title", "UUID: " . $uuid);
            }
            $banlist_set = $avatar_set->relatedBanlist();
        }

        $table_head = ["id","Name","Remove"];
        $table_body = [];

        foreach ($banlist_set as $banlist) {
            $avatar = $avatar_set->getObjectByID($banlist->getAvatarLink());

            $entry = [];
            $entry[] = $banlist->getId();
            $form = new form();
            $form->target("banlist/clear/" . $banlist->getId());
            $form->required(true);
            $entry[] = $avatar->getAvatarName();
            $entry[] = $form->render("Remove", "danger");
            $table_body[] = $entry;
        }
        $this->setSwapTag("page_content", $this->renderDatatable($table_head, $table_body));
        $this->output->addSwapTagString("page_content", "<br/><hr/>");
        $form = new form();
        $form->mode("get");
        $form->target("banlist");
        $form->required(false);
        $form->col(4);
            $form->group("Search: Name or UUID");
            $form->textInput("name", "Name", 30, "", "2 letters min to match");
            $form->textInput("uuid", "SL UUID", 36, "", "a full UUID to match");
        $form1 = $form->render("Start", "info");
        $form = new form();
        $form->target("banlist/create");
        $form->required(true);
        $form->col(4);
            $form->group("Add to ban list");
            $form->textInput(
                "uid",
                "Avatar UID",
                30,
                "",
                ""
            );
            $form->directAdd("<a data-toggle=\"modal\" data-target=\"#AvatarPicker\" "
            . "href=\"#\" target=\"_blank\">Find/Add avatar</a><br/>");
        $form2 = $form->render("Goodbye", "primary");
        $mygrid = new Grid();
        $mygrid->addContent($form1, 6);
        $mygrid->addContent($form2, 6);
        $this->output->addSwapTagString("page_content", $mygrid->getOutput());
    }
}
