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
    public string $whyfailed = "";
    public function createTransaction(
        Avatar $avatar,
        Package $package,
        Stream $stream,
        Reseller $reseller,
        Region $region,
        int $amountpaid,
        bool $renewal = false,
        ?int $forcesetunixtime = null,
        ?string $notes = null
    ): bool {
        $this->whyfailed = "";
        $transaction = new Transactions();
        $uid_transaction = $transaction->createUID("transactionUid", 8);
        if ($uid_transaction->status == false) {
            $this->whyfailed = "Unable to create UID for transaction";
            return false;
        }
        $transaction = new Transactions();
        $transaction->_AvatarLink = $avatar->_Id;
        $transaction->_PackageLink = $package->_Id;
        $transaction->_StreamLink = $stream->_Id;
        $transaction->_ResellerLink = $reseller->_Id;
        $transaction->_RegionLink = $region->_Id;
        $transaction->_Amount = $amountpaid;
        $transaction->_Unixtime = time();
        if ($forcesetunixtime != null) {
            $transaction->_Unixtime = $forcesetunixtime;
        }
        $transaction->_Notes = $notes;
        $transaction->_TransactionUid = $uid_transaction->uid;
        $transaction->_Renew = $renewal;
        $create_status = $transaction->createEntry();
        if ($create_status->status == false) {
            $this->whyfailed = "Unable to save to db";
        }
        return $create_status->status;
    }
}
