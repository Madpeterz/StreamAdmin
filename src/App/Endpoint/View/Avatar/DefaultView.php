<?php

namespace App\Endpoint\View\Avatar;

use App\Models\Sets\AvatarSet;
use YAPF\Bootstrap\Template\Form;
use YAPF\InputFilter\InputFilter as InputFilter;

class DefaultView extends View
{
    public function process(): void
    {

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
        foreach ($avatarSet as $avatar) {
            $entry = [];
            $entry[] = $avatar->getId();
            $entry[] = '<a href="[[SITE_URL]]avatar/manage/' . $avatar->getAvatarUid() . '">'
            . $avatar->getAvatarUid() . '</a>';
            $entry[] = '<a href="[[SITE_URL]]search?search=' . $avatar->getAvatarName() . '">'
            . $avatar->getAvatarName() . '</a>';
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
