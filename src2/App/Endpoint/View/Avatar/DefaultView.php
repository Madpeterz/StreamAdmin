<?php

namespace App\Endpoint\View\Avatar;

use App\R7\Set\AvatarSet;
use App\Template\Form as Form;
use YAPF\InputFilter\InputFilter as InputFilter;

class DefaultView extends View
{
    public function process(): void
    {
        $input = new InputFilter();
        $match_with = "newest";
        $name = $input->getFilter("name");
        $uuid = $input->getFilter("uuid");
        $wherefields = [];
        $wherevalues = [];
        $wheretypes = [];
        $wherematchs = [];
        if (strlen($uuid) == 36) {
            $match_with = "uuid";
            $wherefields = ["avatarUUID"];
            $wherevalues = [$uuid];
            $wheretypes = ["s"];
            $wherematchs = ["="];
        } elseif (strlen($name) >= 2) {
            $match_with = "name";
            $wherefields = ["avatarName"];
            $wherevalues = [$name];
            $wheretypes = ["s"];
            $wherematchs = ["% LIKE %"];
        }
        $avatarSet = new AvatarSet();
        if ($match_with == "newest") {
            $avatarSet->loadNewest(30);
            $this->setSwapTag("page_title", "Newest 30 avatars");
        } else {
            $where_config = [
                "fields" => $wherefields,
                "values" => $wherevalues,
                "types" => $wheretypes,
                "matches" => $wherematchs,
            ];
            $avatarSet->loadWithConfig($where_config);
            if ($match_with == "name") {
                $this->setSwapTag("page_title", "Names containing: " . $name);
            } else {
                $this->setSwapTag("page_title", "UUID: " . $uuid);
            }
        }
        $table_head = ["id","UID","Name"];
        $table_body = [];
        foreach ($avatarSet->getAllIds() as $avatar_id) {
            $avatar = $avatarSet->getObjectByID($avatar_id);
            $entry = [];
            $entry[] = $avatar->getId();
            $entry[] = '<a href="[[url_base]]avatar/manage/' . $avatar->getAvatarUid() . '">'
            . $avatar->getAvatarUid() . '</a>';
            $entry[] = $avatar->getAvatarName();
            $table_body[] = $entry;
        }
        $this->output->addSwapTagString("page_content", $this->renderDatatable($table_head, $table_body));
        $this->output->addSwapTagString("page_content", "<br/><hr/>");
        $form = new Form();
        $form->mode("get");
        $form->target("avatar");
        $form->required(false);
        $form->col(4);
        $form->group("Search: Name or UUID");
        $form->textInput("name", "Name", 30, $name, "2 letters min to match");
        $form->textInput("uuid", "SL UUID", 36, $uuid, "a full UUID to match");
        $this->output->addSwapTagString("page_content", $form->render("Start", "info"));
    }
}
