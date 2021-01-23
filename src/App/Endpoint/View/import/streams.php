<?php

namespace App\Endpoint\View\Import;

use App\Models\PackageSet;
use App\Models\ServerSet;
use App\Models\Stream;
use App\R4\ItemsSet;
use App\R4\PackagesSet;

class Streams extends View
{
    public function process(): void
    {
        $r4_items_set = new ItemsSet();
        $r4_items_set->reconnectSql($this->oldSqlDB);
        $r4_items_set->loadAll();

        $r4_packages_set = new PackagesSet();
        $r4_packages_set->reconnectSql($this->oldSqlDB);
        $r4_packages_set->loadAll();

        $r4_package_id_to_name = $r4_packages_set->getLinkedArray("id", "name");
        $servers_set = new ServerSet();
        $servers_set->loadAll();

        $package_set = new PackageSet();
        $package_set->loadAll();

        $package_name_to_id = $package_set->getLinkedArray("name", "id");
        $server_domain_to_id = $servers_set->getLinkedArray("domain", "id");

        $stream_created = 0;
        $stream_skipped_no_package = 0;
        $stream_skipped_no_server = 0;
        $all_ok = true;

        foreach ($r4_items_set->getAllIds() as $r4_item_id) {
            $r4_item = $r4_items_set->getObjectByID($r4_item_id);
            if (array_key_exists($r4_item->getPackageid(), $r4_package_id_to_name) == true) {
                $stream_skipped_no_package++;
                continue;
            }
            $find_package = "R4|" . $r4_item->getPackageid() . "|" . $r4_package_id_to_name[$r4_item->getPackageid()];
            if (array_key_exists($find_package, $package_name_to_id) == false) {
                $stream_skipped_no_package++;
                continue;
            }
            if (array_key_exists($r4_item->getStreamurl(), $server_domain_to_id) == false) {
                $stream_skipped_no_server++;
                continue;
            }
            $stream = new Stream();
            $uid = $stream->createUID("streamUid", 8, 10);
            if ($uid["status"] == false) {
                $this->output->addSwapTagString("page_content", "Unable to create required stream Uid");
                $all_ok = false;
                break;
            }
            $stream->setStreamUid($uid["uid"]);
            $stream->setPackageLink($package_name_to_id[$find_package]);
            $stream->setServerLink($server_domain_to_id[$r4_item->getStreamurl()]);
            $stream->setPort($r4_item->getStreamport());
            $stream->setNeedWork($r4_item->getBaditem());
            $stream->setAdminPassword($r4_item->getAdminPassword());
            $stream->setAdminUsername($r4_item->getAdminUsername());
            $stream->setOriginalAdminUsername($r4_item->getAdminUsername());
            $stream->setDjPassword($r4_item->getStreampassword());
            $stream->setMountpoint("r4|" . $r4_item->getId() . "");
            $create_status = $stream->createEntry();
            if ($create_status["status"] == false) {
                $this->output->addSwapTagString(
                    "page_content",
                    "Unable to create new entry in database because: " . $create_status["message"]
                );
                $all_ok = false;
                break;
            }
            $stream_created++;
        }
        if ($all_ok == false) {
            $this->sql->flagError();
            return;
        }
        $this->output->addSwapTagString(
            "page_content",
            "Created: " . $stream_created . " streams, "
            . $stream_skipped_no_server . " skipped (No server), "
            . $stream_skipped_no_package . " skipped (No package) <br/> "
            . "<a href=\"[[url_base]]import\">Back to menu</a>"
        );
    }
}
