<?php

namespace App\Endpoint\View\Import;

use App\Helpers\AvatarHelper;
use App\R4\Set\UsersSet as SetUsersSet;

class Avatars extends View
{
    public function process(): void
    {
        $r4_users_set = new SetUsersSet();
        $r4_users_set->reconnectSql($this->oldSqlDB);
        $r4_users_set->loadAll();

        $all_ok = true;
        $avatars_created = 0;
        global $sql;
        $sql = $this->realSqlDB;
        $seen_avatarUUIDs = [];
        foreach ($r4_users_set as $r4_user) {
            if (in_array($r4_user->getSlkey(), $seen_avatarUUIDs) == true) {
                continue;
            }
            $seen_avatarUUIDs[] = $r4_user->getSlkey();
            $avatar_helper = new AvatarHelper();

            $status = $avatar_helper->loadOrCreate($r4_user->getSlkey(), $r4_user->getSlname());
            if ($status == false) {
                $all_ok = false;
                break;
            }
            $avatars_created++;
        }

        if ($all_ok == false) {
            $this->output->addSwapTagString("page_content", "Unable to create one or more avatars");
            $this->sql->flagError();
            return;
        }
        $this->output->addSwapTagString(
            "page_content",
            "Created: " . $avatars_created . " avatars <br/> <a href=\"[[url_base]]import\">Back to menu</a>"
        );
        $this->sql->sqlSave();
    }
}
