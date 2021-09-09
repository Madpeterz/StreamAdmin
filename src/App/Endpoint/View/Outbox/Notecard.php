<?php

namespace App\Endpoint\View\Outbox;

use App\R7\Set\AvatarSet;
use App\R7\Set\NotecardSet;
use App\R7\Set\RentalSet;

class Notecard extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("page_title", " Unsent notecards");
        $table_head = ["id","Rental UID","Avatar name"];
        $table_body = [];
        $notecard_set = new NotecardSet();
        $notecard_set->loadAll();
        $rental_set = new RentalSet();
        $rental_set->loadIds($notecard_set->getAllByField("rentalLink"));
        $avatar_set = new AvatarSet();
        $avatar_set->loadIds($rental_set->getAllByField("avatarLink"));
        foreach ($notecard_set as $notecard) {
            $rental = $rental_set->getObjectByID($notecard->getRentalLink());
            $avatar = $avatar_set->getObjectByID($rental->getAvatarLink());
            $table_body[] = [$notecard->getId(),$rental->getRentalUid(),$avatar->getAvatarName()];
        }
        $this->setSwapTag("page_content", $this->renderDatatable($table_head, $table_body));
    }
}
