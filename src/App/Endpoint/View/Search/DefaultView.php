<?php

namespace App\Endpoints\View\Search;

use App\Models\AvatarSet;
use App\Models\PackageSet;
use App\Models\RentalSet;
use App\Models\ServerSet;
use App\Models\StreamSet;
use YAPF\InputFilter\InputFilter;

class DefaultView extends View
{
    protected Array $pages = [];
    protected Array $seenAvatarIds = [];
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
    }

    protected function renderStreams(StreamSet $search_stream_set, ServerSet $server_set): void
    {
        $table_head = ["UID","Server","Port","Status"];
        $table_body = [];
        $rental_set = new RentalSet();
        $rental_set->loadIds($search_stream_set->getAllByField("rentallink"));
        $avatar_set = new AvatarSet();
        $avatar_set->loadIds($rental_set->getAllByField("avatarlink"));
        $rental_set_ids = $rental_set->getAllIds();
        foreach ($search_stream_set->getAllIds() as $streamid) {
            $stream = $search_stream_set->getObjectByID($streamid);
            $server = $server_set->getObjectByID($stream->getServerlink());
            $entry = [];
            $entry[] = '<a href="[[url_base]]stream/manage/' . $stream->getStream_uid() . '">'
            . $stream->getStream_uid() . '</a>';
            $entry[] = $server->getDomain();
            $entry[] = $stream->getPort();
            if ($stream->getNeedwork() == true) {
                $entry[] = "<span class=\"needwork\">Need work</span>";
            } elseif ($stream->getRentallink() == null) {
                $entry[] = "<span class=\"ready\">Ready</span>";
            } elseif (in_array($stream->getRentallink(), $rental_set_ids) == false) {
                $entry[] = "Rented but cant find rental.";
            } else {
                $rental = $rental_set->getObjectByID($stream->getRentallink());
                $avatar = $avatar_set->getObjectByID($rental->getAvatarlink());
                $av_detail = explode(" ", $avatar->getAvatarname());
                $av_name = $avatar->getAvatarname();
                if ($av_detail[1] == "Resident") {
                    $av_name = $av_detail[0];
                }
                $entry[] = '<a class="sold" href="[[url_base]]client/manage/'
                . $rental->getRental_uid() . '">Sold -> ' . $av_name . '</a>';
            }
        }
        $table_body[] = $entry;
        $pages["Streams [" . $search_stream_set->getCount() . "]"] = render_table($table_head, $table_body);
    }

    protected function renderAvatars(AvatarSet $avatar_set): void
    {
        $table_head = ["UID","Name"];
        $table_body = [];
        foreach ($avatar_set->getAllIds() as $avatar_id) {
            $avatar = $avatar_set->getObjectByID($avatar_id);
            $entry = [];
            $entry[] = '<a href="[[url_base]]avatar/manage/' . $avatar->getAvatar_uid() . '">'
            . $avatar->getAvatar_uid() . '</a>';
            $entry[] = $avatar->getAvatarname();
            $table_body[] = $entry;
        }
        $this->pages["Avatars [" . $avatar_set->getCount() . "]"] = render_table($table_head, $table_body);
    }

    protected function renderRentals(RentalSet $search_rental_set, AvatarSet $avatar_set, StreamSet $stream_set): void
    {
        $table_head = ["Rental UID","Avatar","Port","Notecard","Timeleft/Expired","Renewals"];
        $table_body = [];
        foreach ($search_rental_set->getAllIds() as $rental_id) {
            $rental = $search_rental_set->getObjectByID($rental_id);
            $avatar = $avatar_set->getObjectByID($rental->getAvatarlink());
            $stream = $stream_set->getObjectByID($rental->getStreamlink());
            $entry = [];
            $entry[] = '<a href="[[url_base]]client/manage/'
            . $rental->getRental_uid() . '">' . $rental->getRental_uid() . '</a>';
            $av_detail = explode(" ", $avatar->getAvatarname());
            $avname = $avatar->getAvatarname();
            if ($av_detail[1] == "Resident") {
                $avname = $av_detail[0];
            }
            $entry[] = $avname;
            $entry[] = $stream->getPort();
            $entry[] = "<button type=\"button\" class=\"btn btn-sm btn-outline-light\" "
            . "data-toggle=\"modal\" data-target=\"#NotecardModal\" data-rentaluid=\""
            . $rental->getRental_uid() . "\">View</button>";
            $timeleft = "Expired - " . expired_ago($rental->getExpireunixtime());
            if ($rental->getExpireunixtime() > time()) {
                $timeleft  = "Active - " . timeleft_hours_and_days($rental->getExpireunixtime());
            }
            $entry[] = $timeleft;
            $entry[] = $rental->getRenewals();
            $table_body[] = $entry;
        }
        $this->pages["Clients [" . $search_rental_set->getCount() . "]"] = render_table($table_head, $table_body);
    }

    protected function loadPackagesFromStreams(StreamSet $stream_set): PackageSet
    {
        $package_set = new PackageSet();
        $package_set->loadIds($stream_set->getAllByField("packagelink"));
        return $package_set;
    }

    protected function loadStreamsFromRentals(RentalSet $search_rental_set): StreamSet
    {
        $stream_set = new StreamSet();
        $stream_set->loadIds($search_rental_set->getAllByField("streamlink"));
        return $stream_set;
    }

    protected function loadAvatarsFromRentals(RentalSet $search_rental_set): AvatarSet
    {
        $avatar_set = new AvatarSet();
        $avatar_set->loadIds($search_rental_set->getAllByField("avatarlink"));
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
        $entry = $search_stream_set->getUniqueArray("rentallink");
        $seen = $search_rental_set->getUniqueArray("id");
        $repeat_search_entrys = [];
        foreach ($entry as $rentallink) {
            if (in_array($rentallink, $seen) == false) {
                $repeat_search_entrys[] = $rentallink;
                $seen[] = $rentallink;
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
            "fields" => ["adminusername","port","stream_uid"],
            "matches" => ["% LIKE %","LIKE","% LIKE %"],
            "values" => [$search,$search,$search],
            "types" => ["s","i","s"],
            "join_with" => ["OR","OR"],
        ];
        if ($search_rental_set->getCount() > 0) {
            $where_config["fields"][] = "id";
            $where_config["matches"][] = "IN";
            $where_config["values"][] = $search_rental_set->getUniqueArray("streamlink");
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
            "fields" => ["avataruuid","avatarname","avatar_uid"],
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
            "fields" => ["rental_uid","message"],
            "matches" => ["% LIKE %","% LIKE %"],
            "values" => [$search,$search],
            "types" => ["s","s"],
            "join_with" => ["OR"],
        ];
        if ($search_avatar_set->getCount() > 0) {
            $where_config["fields"][] = "avatarlink";
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
