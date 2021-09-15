<?php

namespace App\Endpoint\View\Search;

use App\R7\Set\AvatarSet;
use App\R7\Set\PackageSet;
use App\R7\Set\RentalSet;
use App\R7\Set\ServerSet;
use App\R7\Set\StreamSet;
use App\Template\PagedInfo;
use YAPF\InputFilter\InputFilter;

class DefaultView extends View
{
    protected array $pages = [];
    protected array $seenAvatarIds = [];
    public function process(): void
    {
        $input = new InputFilter();
        $search = $input->getFilter("search");
        if (strlen($search) < 3) {
            $this->setSwapTag("page_content", "Sorry search requires 3 or more letters");
            return;
        }
        $this->setSwapTag("page_title", "Search results for: " . $search);

        $server_set = $this->loadServers();
        $search_avatar_set = $this->loadAvatars($search);
        $search_rental_set = $this->loadClients($search, $search_avatar_set);
        $search_stream_set = $this->loadStreams($search, $search_rental_set);
        $search_rental_set_again = $this->loadClientsLinked($search_stream_set, $search_rental_set);
        $search_rental_set = $this->mergeRentalCollections($search_rental_set_again, $search_rental_set);
        $avatar_set = $this->loadAvatarsFromRentals($search_rental_set);
        $avatar_set = $this->mergeAvatarCollections($avatar_set, $search_avatar_set);
        $stream_set = $this->loadStreamsFromRentals($search_rental_set);
        $package_set = $this->loadPackagesFromStreams($stream_set);
        $stream_set = $this->mergeStreamCollections($stream_set, $search_stream_set);
        $this->renderRentals($search_rental_set, $avatar_set, $stream_set);
        $this->renderAvatars($avatar_set);
        $this->renderStreams($stream_set, $server_set);
        $paged = new PagedInfo();
        $this->setSwapTag("page_content", $paged->render($this->pages));
    }

    protected function renderStreams(StreamSet $search_stream_set, ServerSet $server_set): void
    {
        $table_head = ["UID","Server","Port","Status"];
        $table_body = [];
        $rental_set = new RentalSet();
        $rental_set->loadIds($search_stream_set->getAllByField("rentalLink"));
        $avatar_set = new AvatarSet();
        $avatar_set->loadIds($rental_set->getAllByField("avatarLink"));
        $rental_set_ids = $rental_set->getAllIds();
        foreach ($search_stream_set as $stream) {
            $server = $server_set->getObjectByID($stream->getServerLink());
            $entry = [];
            $entry[] = '<a href="[[url_base]]stream/manage/' . $stream->getStreamUid() . '">'
            . $stream->getStreamUid() . '</a>';
            $entry[] = $server->getDomain();
            $entry[] = $stream->getPort();
            if ($stream->getNeedWork() == true) {
                $entry[] = "<span class=\"needWork\">Need work</span>";
            } elseif ($stream->getRentalLink() == null) {
                $entry[] = "<span class=\"ready\">Ready</span>";
            } elseif (in_array($stream->getRentalLink(), $rental_set_ids) == false) {
                $entry[] = "Rented but cant find rental.";
            } else {
                $rental = $rental_set->getObjectByID($stream->getRentalLink());
                $avatar = $avatar_set->getObjectByID($rental->getAvatarLink());
                $av_detail = explode(" ", $avatar->getAvatarName());
                $av_name = $avatar->getAvatarName();
                if ($av_detail[1] == "Resident") {
                    $av_name = $av_detail[0];
                }
                $entry[] = '<a class="sold" href="[[url_base]]client/manage/'
                . $rental->getRentalUid() . '">Sold -> ' . $av_name . '</a>';
            }
            $table_body[] = $entry;
        }
        $this->pages["Streams [" . $search_stream_set->getCount() . "]"] = $this->renderTable($table_head, $table_body);
    }

    protected function renderAvatars(AvatarSet $avatar_set): void
    {
        $table_head = ["UID","Name"];
        $table_body = [];
        foreach ($avatar_set as $avatar) {
            $entry = [];
            $entry[] = '<a href="[[url_base]]avatar/manage/' . $avatar->getAvatarUid() . '">'
            . $avatar->getAvatarUid() . '</a>';
            $entry[] = $avatar->getAvatarName();
            $table_body[] = $entry;
        }
        $this->pages["Avatars [" . $avatar_set->getCount() . "]"] = $this->renderTable($table_head, $table_body);
    }

    protected function renderRentals(RentalSet $search_rental_set, AvatarSet $avatar_set, StreamSet $stream_set): void
    {
        $table_head = ["Rental UID","Avatar","Port","Notecard","Timeleft/Expired","Renewals"];
        $table_body = [];
        foreach ($search_rental_set as $rental) {
            $avatar = $avatar_set->getObjectByID($rental->getAvatarLink());
            $stream = $stream_set->getObjectByID($rental->getStreamLink());
            $entry = [];
            $entry[] = '<a href="[[url_base]]client/manage/'
            . $rental->getRentalUid() . '">' . $rental->getRentalUid() . '</a>';
            $av_detail = explode(" ", $avatar->getAvatarName());
            $avname = $avatar->getAvatarName();
            if ($av_detail[1] == "Resident") {
                $avname = $av_detail[0];
            }
            $entry[] = $avname;
            $entry[] = $stream->getPort();
            $entry[] = "<button type=\"button\" class=\"btn btn-sm btn-outline-light\" "
            . "data-toggle=\"modal\" data-target=\"#NotecardModal\" data-rentaluid=\""
            . $rental->getRentalUid() . "\">View</button>";
            $timeleft = "Expired - " . expiredAgo($rental->getExpireUnixtime());
            if ($rental->getExpireUnixtime() > time()) {
                $timeleft  = "Active - " . timeleftHoursAndDays($rental->getExpireUnixtime());
            }
            $entry[] = $timeleft;
            $entry[] = $rental->getRenewals();
            $table_body[] = $entry;
        }
        $this->pages["Clients [" . $search_rental_set->getCount() . "]"] = $this->renderTable($table_head, $table_body);
    }

    protected function loadPackagesFromStreams(StreamSet $stream_set): PackageSet
    {
        $package_set = new PackageSet();
        $package_set->loadIds($stream_set->getAllByField("packageLink"));
        return $package_set;
    }

    protected function loadStreamsFromRentals(RentalSet $search_rental_set): StreamSet
    {
        $stream_set = new StreamSet();
        $stream_set->loadIds($search_rental_set->getAllByField("streamLink"));
        return $stream_set;
    }

    protected function loadAvatarsFromRentals(RentalSet $search_rental_set): AvatarSet
    {
        $avatar_set = new AvatarSet();
        $avatar_set->loadIds($search_rental_set->getAllByField("avatarLink"));
        return $avatar_set;
    }

    protected function mergeStreamCollections(StreamSet $a, StreamSet $b): StreamSet
    {
        foreach ($b->getAllIds() as $stream_id) {
            $stream = $b->getObjectByID($stream_id);
            $a->addToCollected($stream);
        }
        return $a;
    }

    protected function mergeRentalCollections(RentalSet $a, RentalSet $b): RentalSet
    {
        foreach ($b->getAllIds() as $rental_id) {
            $rental = $b->getObjectByID($rental_id);
            $a->addToCollected($rental);
        }
        return $a;
    }

    protected function mergeAvatarCollections(AvatarSet $a, AvatarSet $b): AvatarSet
    {
        foreach ($b->getAllIds() as $avatar_id) {
            $avatar = $b->getObjectByID($avatar_id);
            $a->addToCollected($avatar);
        }
        return $a;
    }

    protected function loadClientsLinked(StreamSet $search_stream_set, RentalSet $search_rental_set): RentalSet
    {
        $search_rental_set_again = new RentalSet();
        $entry = $search_stream_set->getUniqueArray("rentalLink");
        $seen = $search_rental_set->getUniqueArray("id");
        $repeat_search_entrys = [];
        foreach ($entry as $rentalLink) {
            if (in_array($rentalLink, $seen) == false) {
                $repeat_search_entrys[] = $rentalLink;
                $seen[] = $rentalLink;
            }
        }
        if (count($repeat_search_entrys) > 0) {
            $where_config = [
                "fields" => ["id"],
                "matches" => ["IN"],
                "values" => [$repeat_search_entrys],
                "types" => ["i"],
            ];
            $search_rental_set_again->loadWithConfig($where_config);
        }
        return $search_rental_set_again;
    }

    protected function loadStreams(string $search, RentalSet $search_rental_set): StreamSet
    {
        $where_config = [
            "fields" => ["adminUsername","port","streamUid"],
            "matches" => ["% LIKE %","=","% LIKE %"],
            "values" => [$search,$search,$search],
            "types" => ["s","i","s"],
            "join_with" => ["OR","OR"],
        ];
        if ($search_rental_set->getCount() > 0) {
            $where_config["fields"][] = "id";
            $where_config["matches"][] = "IN";
            $where_config["values"][] = $search_rental_set->getUniqueArray("streamLink");
            $where_config["types"][] = "i";
            $where_config["join_with"][] = "OR";
        }
        $search_stream_set = new StreamSet();
        $search_stream_set->loadWithConfig($where_config);
        return $search_stream_set;
    }

    protected function loadServers(): ServerSet
    {
        $server_set = new ServerSet();
        $server_set->loadAll();
        return $server_set;
    }


    protected function loadAvatars(string $search): AvatarSet
    {
        $where_config = [
            "fields" => ["avatarUUID","avatarName","avatarUid"],
            "matches" => ["% LIKE %","% LIKE %","% LIKE %"],
            "values" => [$search,$search,$search],
            "types" => ["s","s","s"],
            "join_with" => ["OR","OR"],
        ];
        $search_avatar_set = new AvatarSet();
        $search_avatar_set->loadWithConfig($where_config);
        return $search_avatar_set;
    }

    protected function loadClients(string $search, AvatarSet $search_avatar_set): RentalSet
    {
        $where_config = [
            "fields" => ["rentalUid","message"],
            "matches" => ["% LIKE %","% LIKE %"],
            "values" => [$search,$search],
            "types" => ["s","s"],
            "join_with" => ["OR"],
        ];
        if ($search_avatar_set->getCount() > 0) {
            $where_config["fields"][] = "avatarLink";
            $where_config["matches"][] = "IN";
            $where_config["values"][] = $search_avatar_set->getUniqueArray("id");
            $where_config["types"][] = "i";
            $where_config["join_with"][] = "OR";
        }
        $search_rental_set = new RentalSet();
        $search_rental_set->loadWithConfig($where_config);
        return $search_rental_set;
    }
}
