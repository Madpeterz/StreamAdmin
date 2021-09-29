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
        $rental_set->loadByValues($notecard_set->getAllByField("rentalLink"));
        $avatar_set = new AvatarSet();
        $avatar_set->loadByValues($rental_set->getAllByField("avatarLink"));
        foreach ($notecard_set as $notecard) {
            $rental = $rental_set->getObjectByID($notecard->getRentalLink());
            $avatar = $avatar_set->getObjectByID($rental->getAvatarLink());
            $entry = [];
            $entry[] = $notecard->getId();
            $entry[] = $rental->getRentalUid();
            $entry[] = '<a href="[[url_base]]search?search=' . $avatar->getAvatarName() . '">'
            . $avatar->getAvatarName() . '</a>';
            $table_body[] = $entry;
        }
        $this->setSwapTag("page_content", $this->renderDatatable($table_head, $table_body));
    }
}
