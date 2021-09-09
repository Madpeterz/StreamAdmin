<?php

namespace App\Endpoint\Control\Server;

use App\Helpers\ServerApi\ServerApiHelper;
use App\R7\Model\Apis;
use App\R7\Model\Server;
use App\R7\Set\StreamSet;
use App\Template\ViewAjax;

class SyncAccounts extends ViewAjax
{
    public function process(): void
    {
        $server = new Server();
        $stream_set = new StreamSet();
        $api = new Apis();
        $serverapi_helper = new ServerApiHelper();

        if ($server->loadID($this->page) == false) {
            $this->failed("Unable to find server");
            return;
        }
        if ($api->loadID($server->getApiLink()) == false) {
            $this->failed("Unable to find api used by server");
            return;
        }
        if (($server->getApiSyncAccounts() == false) || ($api->getApiSyncAccounts() == false)) {
            $this->failed("Server or API have sync accounts disabled");
            return;
        }
        if ($serverapi_helper->forceSetServer($server) == false) {
            $this->failed("Unable to attach server to api helper");
            return;
        }
        $oneday_ago = time() - ((60 * 60) * 24);
        $where_config = [
        "fields" => ["serverLink","lastApiSync"],
        "matches" => ["=","<="],
        "values" => [$server->getId(),$oneday_ago],
        "types" => ["i","i"],
        ];
        $limits = [
        "page_number" => 0,
        "max_entrys" => 10,
        ];

        $stream_set->loadWithConfig($where_config, null, $limits);
        if ($stream_set->getCount() == 0) {
            $this->setSwapTag(
                "message",
                "Unable to find any streams attached to server or all streamed sync'd in the last 24 hours"
            );
            return;
        }
        $accounts_found = $serverapi_helper->getAllAccounts(true, $stream_set);
        if ($accounts_found["status"] == false) {
            $this->failed($serverapi_helper->getMessage());
            return;
        }
        $accounts_updated = 0;
        $accounts_insync = 0;
        $accounts_missing_global = 0;
        $accounts_missing_passwords = 0;
        $all_ok = true;
        foreach ($stream_set as $stream) {
            if (in_array($stream->getAdminUsername(), $accounts_found["usernames"]) == false) {
                $accounts_missing_global++;
                continue;
            }
            if (array_key_exists($stream->getAdminUsername(), $accounts_found["passwords"]) == false) {
                $accounts_missing_passwords++;
                continue;
            }
            $admpw = $accounts_found["passwords"][$stream->getAdminUsername()]["admin"];
            $djpw = $accounts_found["passwords"][$stream->getAdminUsername()]["dj"];
            if ($stream->getAdminPassword() != $admpw) {
                $stream->setAdminPassword($admpw);
            }
            if ($stream->getDjPassword() != $djpw) {
                $stream->setDjPassword($djpw);
            }
            $stream->setLastApiSync(time());
            $update_status = $stream->updateEntry();
            if ($update_status["status"] == false) {
                $all_ok = false;
                $this->failed("failed to sync password to db");
                break;
            }
            $accounts_updated++;
        }
        if ($all_ok == false) {
            return;
        }
        $server->setLastApiSync(time());
        $update_status = $server->updateEntry();
        if ($update_status["status"] == false) {
            $this->failed("Unable to update server last sync time");
            return;
        }
        $this->ok("Updated: " . $accounts_updated . " / Ok: " . $accounts_insync . "");
        if ($accounts_missing_passwords > 0) {
            $this->output->addSwapTagString("message", " / Missing PW dataset: " . $accounts_missing_passwords);
        }
        if ($accounts_missing_global > 0) {
            $this->output->addSwapTagString("message", " / Account missing: " . $accounts_missing_global);
        }
    }
}
