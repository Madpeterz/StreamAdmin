<?php

namespace App\Endpoint\Control\Banlist;

use App\Models\Sets\AvatarSet;
use App\Models\Banlist;
use App\Framework\ViewAjax;

class Create extends ViewAjax
{
    public function process(): void
    {

        $avataruid = $this->input->post("uid");
        if ($avataruid == null) {
            $this->failed("Avatar UID/Name/UUID was not sent!");
            return;
        }
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
            $this->failed("Unable to find avatar to attach to ban do they exist under avatars?");
            return;
        }
        $avatar = $avatar_set->getFirst();
        $banlist = new Banlist();
        if ($banlist->loadByAvatarLink($avatar->getId()) == true) {
            $this->failed("The target avatar is already banned");
            return;
        }
        $banlist = new Banlist();
        $banlist->setAvatarLink($avatar->getId());
        $create_status = $banlist->createEntry();
        if ($create_status["status"] == false) {
            $this->failed("Unable to create a new entry in the ban list");
            return;
        }
        $this->setSwapTag("redirect", "banlist");
        $this->ok("Entry created");
    }
}
