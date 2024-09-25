<?php

namespace App\Endpoint\View\Avatar;

use App\Models\Sets\AvatarSet;
use YAPF\Bootstrap\Template\Form;

class DefaultView extends View
{
    protected ?avatarSet $avatarSet = null;
    protected ?string $name = "";
    protected ?string $uuid = "";
    protected function searchMatch(): bool
    {
        $this->name = $this->input->get("name")->checkStringLengthMin(2)->asString();
        $this->uuid = $this->input->get("uuid")->isUuid()->asString();
        $whereConfig = null;
        if ($this->name != null) {
            $whereConfig = [
                "fields" => ["avatarName"],
                "values" => [$this->name],
                "types" => ["s"],
                "matches" => ["% LIKE %"],
            ];
            $this->setSwapTag("page_title", "Names containing: " . $this->name);
        } elseif ($this->uuid != null) {
            $whereConfig = [
                "fields" => ["avatarUUID"],
                "values" => [$this->uuid],
                "types" => ["s"],
                "matches" => ["="],
            ];
            $this->setSwapTag("page_title", "UUID: " . $this->uuid);
        }
        if ($whereConfig === null) {
            return false;
        }
        return $this->avatarSet->loadWithConfig($whereConfig)->status;
    }
    public function process(): void
    {
        $this->avatarSet = new avatarSet();
        $load = $this->searchMatch();
        if ($load == false) {
            $this->avatarSet->loadNewest(30);
            $this->setSwapTag("page_title", "Newest 30 avatars");
        }

        $table_head = ["id","UID","Name"];
        $table_body = [];
        foreach ($this->avatarSet as $avatar) {
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
        $form->textInput("name", "Name", 30, $this->name, "2 letters min to match");
        $form->textInput("uuid", "SL UUID", 36, $this->uuid, "a full UUID to match");
        $this->output->addSwapTagString("page_content", $form->render("Start", "info"));
    }
}
