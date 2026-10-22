<?php

namespace App\Endpoint\View\Stream;

use App\Models\Set\PackageSet;
use App\Models\Set\RentalSet;
use App\Models\Set\StreamSet;

abstract class RenderList extends View
{
    protected RentalSet $rentalSet;
    protected StreamSet $streamSet;
    protected PackageSet $packageSet;
    protected array $rental_set_ids = [];
    public function process(bool $usePackageNotServer = false): void
    {
        $table_head = ["id","UID","Server","Port","Status"];
        $table_body = [];
        if ($usePackageNotServer == true) {
            $table_head = ["id","UID","Package","Port","Status"];
        }

        $avatar_set = $this->rentalSet->relatedAvatar();
        $server_set = $this->streamSet->relatedServer();

        foreach ($this->streamSet as $stream) {
            $server = $server_set->getObjectByID($stream->_ServerLink);
            $entry = [];
            $entry[] = $stream->_Id;
            $entry[] = '<a href="[[SITE_URL]]stream/manage/' . $stream->_StreamUid . '">'
            . $stream->_StreamUid . '</a>';
            $midpoint = $server->_Domain;
            if ($usePackageNotServer == true) {
                $package = $this->packageSet->getObjectByID($stream->_PackageLink);
                $midpoint = $package->_Name;
            }
            $entry[] = $midpoint;
            $entry[] = $stream->_Port;
            $state = "Rented but cant find rental.";
            if ($stream->_NeedWork == true) {
                $state = "<span class=\"needWork\">Need work</span>";
            } elseif ($stream->_RentalLink == null) {
                $state = "<span class=\"ready\">Available</span>";
            } elseif (in_array($stream->_RentalLink, $this->rental_set_ids) == true) {
                $rental = $this->rentalSet->getObjectByID($stream->_RentalLink);
                $avatar = $avatar_set->getObjectByID($rental->_AvatarLink);
                $av_detail = explode(" ", $avatar->_AvatarName);
                $av_name = $avatar->_AvatarName;
                if ($av_detail[1] == "Resident") {
                    $av_name = $av_detail[0];
                }
                $state = '<a class="sold" href="[[SITE_URL]]client/manage/'
                . $rental->_RentalUid . '">Sold -> ' . $av_name . '</a>';
            }
            $entry[] = $state;
            $table_body[] = $entry;
        }
        $this->setSwapTag("page_content", $this->renderDatatable($table_head, $table_body, 4));
    }
}
