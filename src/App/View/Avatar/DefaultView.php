<?php

namespace App\View\Avatar;

use App\AvatarSet;
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
            $wherefields = ["avataruuid"];
            $wherevalues = [$uuid];
            $wheretypes = ["s"];
            $wherematchs = ["="];
        } elseif (strlen($name) >= 2) {
            $match_with = "name";
            $wherefields = ["avatarname"];
            $wherevalues = [$name];
            $wheretypes = ["s"];
            $wherematchs = ["% LIKE %"];
        }
        $avatarSet = new AvatarSet();
        if ($match_with == "newest") {
            $avatarSet->loadNewest(30);
            $this->output->setSwapTagString("page_title", "Newest 30 avatars");
        } else {
            $where_config = [
                "fields" => $wherefields,
                "values" => $wherevalues,
                "types" => $wheretypes,
                "matches" => $wherematchs,
            ];
            $avatarSet->loadWithConfig($where_config);
            if ($match_with == "name") {
                $this->output->setSwapTagString("page_title", "Names containing: " . $name);
            } else {
                $this->output->setSwapTagString("page_title", "UUID: " . $uuid);
            }
        }
        $table_head = ["id","UID","Name"];
        $table_body = [];
        foreach ($avatarSet->getAllIds() as $avatar_id) {
            $avatar = $avatarSet->getObjectByID($avatar_id);
            $entry = [];
            $entry[] = $avatar->getId();
            $entry[] = '<a href="[[url_base]]avatar/manage/' . $avatar->getAvatar_uid() . '">'
            . $avatar->getAvatar_uid() . '</a>';
            $entry[] = $avatar->getAvatarname();
            $table_body[] = $entry;
        }
        $this->output->addSwapTagString("page_content", render_datatable($table_head, $table_body));
        $this->output->addSwapTagString("page_content", "<br/><hr/>");
        $form = new Form();
        $form->mode("get");
        $form->target("avatar");
        $form->required(false);
        $form->col(4);
        $form->group("Search: Name or UUID");
        $form->textInput("name", "Name", 30, "", "2 letters min to match");
        $form->textInput("uuid", "SL UUID", 36, "", "a full UUID to match");
        $this->output->addSwapTagString("page_content", $form->render("Start", "info"));
    }
}
