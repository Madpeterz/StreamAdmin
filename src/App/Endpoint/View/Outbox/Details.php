<?php

namespace App\Endpoint\View\Outbox;

use App\R7\Set\AvatarSet;
use App\R7\Set\DetailSet;
use App\R7\Set\RentalSet;

class Details extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("page_title", " Unsent details");
        $table_head = ["id","Rental UID","Avatar name"];
        $table_body = [];
        $detail_set = new DetailSet();
        $detail_set->loadAll();
        $rental_set = new RentalSet();
        $rental_set->loadByValues($detail_set->getAllByField("rentalLink"));
        $avatar_set = new AvatarSet();
        $avatar_set->loadByValues($rental_set->getAllByField("avatarLink"));
        foreach ($detail_set as $detail) {
            $rental = $rental_set->getObjectByID($detail->getRentalLink());
            $avatar = $avatar_set->getObjectByID($rental->getAvatarLink());
            $entry = [];
            $entry[] = $detail->getId();
            $entry[] = $rental->getRentalUid();
            $entry[] = '<a href="[[url_base]]search?search=' . $avatar->getAvatarName() . '">'
            . $avatar->getAvatarName() . '</a>';
            $table_body[] = $entry;
        }
        $this->setSwapTag("page_content", $this->renderDatatable($table_head, $table_body));
    }
}
