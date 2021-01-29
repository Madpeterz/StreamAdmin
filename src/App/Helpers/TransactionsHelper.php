<?php

namespace App\Helpers;

use App\Models\Avatar;
use App\Models\Package;
use App\Models\Region;
use App\Models\Reseller;
use App\Models\Stream;
use App\Models\Transactions;

class TransactionsHelper
{
    public function createTransaction(
        Avatar $avatar,
        Package $package,
        Stream $stream,
        Reseller $reseller,
        Region $region,
        int $amountpaid,
        bool $renewal = false,
        ?int $forcesetunixtime = null
    ): bool {
        $transaction = new Transactions();
        $uid_transaction = $transaction->createUID("transactionUid", 8, 10);
        if ($uid_transaction["status"] == false) {
            return false;
        }
        $transaction->setAvatarLink($avatar->getId());
        $transaction->setPackageLink($package->getId());
        $transaction->setStreamLink($stream->getId());
        $transaction->setResellerLink($reseller->getId());
        $transaction->setRegionLink($region->getId());
        $transaction->setAmount($amountpaid);
        $transaction->setUnixtime(time());
        if ($forcesetunixtime != null) {
            $transaction->setUnixtime($forcesetunixtime);
        }
        $transaction->setTransactionUid($uid_transaction["uid"]);
        $transaction->setRenew($renewal);
        $create_status = $transaction->createEntry();
        return $create_status["status"];
    }
}
