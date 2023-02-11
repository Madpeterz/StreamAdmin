<?php

namespace App\Endpoint\View\Health;

use App\Models\Region;
use App\Models\Sets\ObjectsSet;
use App\Models\Sets\ResellerSet;

class Detailed extends View
{
    protected array $owner_objects_list = [];
    public function process(): void
    {
        $region = new Region();
        if ($region->loadID($this->siteConfig->getPage()) == false) {
            $this->setSwapTag("page_content", "Unknown region please go back and select a option");
            return;
        }
        $this->setSwapTag("html_title", "Health / Detailed");
        $this->setSwapTag("page_title", "<i class=\"fas fa-heartbeat\"></i> Health / Detailed / Region: "
        . $region->getName());

        $this->owner_objects_list = [
            "mailserver",
            "noticeserver",
            "detailsserver",
            "notecardsserver",
            "eventsserver",
        ];
        $resellers = new ResellerSet();
        $resellers->loadAll();
        $avatars = $resellers->relatedAvatar();
        $venderHealth = new ObjectsSet();
        $whereConfig = [
            "fields" => ["avatarLink","objectMode","regionLink"],
            "matches" => ["IN","NOT IN","="],
            "values" => [$avatars->getAllIds(),$this->owner_objects_list,$region->getId()],
            "types" => ["i","s","i"],
        ];
        $venderHealth->loadWithConfig($whereConfig);

        $table_head = ["id","Object","Last seen","Type","Status","Owner"];
        $table_body = [];
        $goodMinTime = time() - 120;
        foreach ($venderHealth as $object) {
            $avatar = $avatars->getObjectByID($object->getAvatarLink());
            $entry = [];
            $entry[] = $object->getId();
            $entry[] = $object->getObjectName();
            $entry[] = date('l jS \of F Y h:i:s A', $object->getLastSeen());
            $statusText = "Good";
            $statusColor = "success";
            if ($object->getLastSeen() < $goodMinTime) {
                $statusColor = "warning";
                $statusText = "MIA";
            }
            $entry[] = $object->getObjectMode();
            $entry[] = "<span class=\"text-" . $statusColor . "\">" . $statusText . "</span>";
            $entry[] = '<a href="[[SITE_URL]]search?search=' . $avatar->getAvatarName() . '">'
            . $avatar->getAvatarName() . '</a>';
            $table_body[] = $entry;
        }

        $this->setSwapTag("page_content", $this->renderDatatable($table_head, $table_body, 2));
    }
}
