<?php

namespace App\Control\Banlist;

use App\Models\AvatarSet;
use App\Models\Banlist;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Create extends ViewAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $avataruid = $input->postFilter("uid");
        $avatar_where_config = [
            "fields" => ["avatar_uid","avatarname","avataruuid"],
            "matches" => ["=","=","="],
            "values" => [$avataruid,$avataruid,$avataruid],
            "types" => ["s","s","s"],
            "join_with" => ["(OR)","(OR)"],
        ];
        $avatar_set = new AvatarSet();
        $avatar_set->loadWithConfig($avatar_where_config);

        if ($avatar_set->getCount() != 1) {
            $this->output->setSwapTagString(
                "message",
                "Unable to find avatar to attach to ban do they exist under avatars?"
            );
            return;
        }
        $avatar = $avatar_set->getFirst();
        $banlist = new banlist();
        if ($banlist->loadByField("avatar_link", $avatar->getId()) == true) {
            $this->output->setSwapTagString("message", "The target avatar is already banned");
            return;
        }
        $banlist = new banlist();
        $banlist->setAvatar_link($avatar->getId());
        $create_status = $banlist->createEntry();
        if ($create_status["status"] == false) {
            $this->output->setSwapTagString("message", "Unable to create a new entry in the ban list");
            return;
        }
        $this->output->setSwapTagString("status", "true");
        $this->output->setSwapTagString("message", "Banlist entry created");
        $this->output->setSwapTagString("redirect", "banlist");
    }
}
