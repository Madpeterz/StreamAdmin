<?php

namespace App\Endpoint\Control\Banlist;

use App\R7\Set\AvatarSet;
use App\R7\Model\Banlist;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Create extends ViewAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $avataruid = $input->postFilter("uid");
        $avatar_where_config = [
            "fields" => ["avatarUid","avatarName","avatarUUID"],
            "matches" => ["=","=","="],
            "values" => [$avataruid,$avataruid,$avataruid],
            "types" => ["s","s","s"],
            "join_with" => ["OR","OR"],
        ];
        $avatar_set = new AvatarSet();
        $avatar_set->loadWithConfig($avatar_where_config);

        if ($avatar_set->getCount() != 1) {
            $this->setSwapTag(
                "message",
                "Unable to find avatar to attach to ban do they exist under avatars?"
            );
            return;
        }
        $avatar = $avatar_set->getFirst();
        $banlist = new Banlist();
        if ($banlist->loadByField("avatarLink", $avatar->getId()) == true) {
            $this->setSwapTag("message", "The target avatar is already banned");
            return;
        }
        $banlist = new Banlist();
        $banlist->setAvatarLink($avatar->getId());
        $create_status = $banlist->createEntry();
        if ($create_status["status"] == false) {
            $this->setSwapTag("message", "Unable to create a new entry in the ban list");
            return;
        }
        $this->setSwapTag("status", true);
        $this->setSwapTag("message", "Banlist entry created");
        $this->setSwapTag("redirect", "banlist");
    }
}
