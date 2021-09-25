<?php

namespace App\Endpoint\View\Import;

use App\R4\Set\Sales_trackingSet;
use App\R7\Model\Transactions as ModelTransactions;
use App\R7\Set\AvatarSet;

class Transactions extends View
{
    public function process(): void
    {
        ini_set('memory_limit', '256M');
        set_time_limit(0);
        $r4_sales_tracking_set = new Sales_trackingSet();
        $r4_sales_tracking_set->reconnectSql($this->oldSqlDB);
        $r4_sales_tracking_set->loadAll();

        global $sql;
        $sql = $this->realSqlDB;

        $avatars = new AvatarSet();
        $avatars->loadAll();

        $avatarName_to_id = $avatars->getLinkedArray("avatarName", "id");

        $all_ok = true;
        $transactions_created = 0;

        foreach ($r4_sales_tracking_set as $r4_sales_tracking) {
            $avatar_id = 1;
            if (array_key_exists($r4_sales_tracking->getSLname(), $avatarName_to_id) == true) {
                $avatar_id = $avatarName_to_id[$r4_sales_tracking->getSLname()];
            }
            $transaction = new ModelTransactions();
            $uid_transaction = $transaction->createUID("transactionUid", 8, 10);
            if ($uid_transaction["status"] == false) {
                $this->output->addSwapTagString(
                    "page_content",
                    "Unable to create transaction because: unable to assign it a uid"
                );
                $all_ok = false;
                break;
            }
            $date = explode("/", $r4_sales_tracking->getDate());
            $time = explode(":", $r4_sales_tracking->getTime());
            $unixtime = time();
            if (count($date) == 3) {
                if (count($time) != 3) {
                    $time = [0,0,1];
                }
                $unixtime = mktime($time[0], $time[1], $time[2], $date[1], $date[2], $date[0]);
            }
            $transaction->setAvatarLink($avatar_id);
            $transaction->setPackageLink(null);
            $transaction->setStreamLink(null);
            $transaction->setResellerLink(null);
            $transaction->setRegionLink(null);
            $transaction->setAmount($r4_sales_tracking->getAmount());
            $transaction->setUnixtime($unixtime);
            $transaction->setTransactionUid($uid_transaction["uid"]);
            $transaction->setRenew($r4_sales_tracking->getSalemode());
            $create_status = $transaction->createEntry();
            if ($create_status["status"] == false) {
                $this->output->addSwapTagString(
                    "page_content",
                    "Unable to create transaction because: " . $create_status["message"]
                );
                $all_ok = false;
                break;
            }
            $transactions_created++;
        }
        if ($all_ok == false) {
            $this->sql->flagError();
            return;
        }
        $this->output->addSwapTagString(
            "page_content",
            "Created: " . $transactions_created . " transactions <br/> <a href=\"[[url_base]]import\">Back to menu</a>"
        );
    }
}
