<?php

namespace App\Endpoint\Ans\Ans;

use App\Helpers\AvatarHelper;
use App\Helpers\BotHelper;
use App\Models\Avatar;
use App\Models\Marketplacecoupons;
use App\Models\Transactions;
use App\Template\ControlAjax;

class Event extends ControlAjax
{
    public function process(): void
    {
        if ($this->siteConfig->getSlConfig()->_EnableCoupons == false) {
            $this->failed("Failed ANS disabled");
            return;
        }
        if (array_key_exists("HTTP_X_ANS_VERIFY_HASH", $_SERVER) == false) {
            $this->failed("VERIFY_HASH has not been set on the system");
            return;
        }
        $checkHash = $_SERVER['HTTP_X_ANS_VERIFY_HASH'];
        if ($checkHash == null) {
            $this->failed("VERIFY_HASH is empty");
            return;
        }
        if (array_key_exists("QUERY_STRING", $_SERVER) == false) {
            $this->failed("QUERY_STRING has not been set on the system");
            return;
        }
        $payerName = $this->input->get("PayerName")->asString();
        $payerKey = $this->input->get("PayerKey")->isUuid()->asString();
        $receiverName = $this->input->get("ReceiverName")->asString();
        $receiverKey = $this->input->get("ReceiverKey")->isUuid()->asString();
        $transactionId = $this->input->get("TransactionID")->checkStringLength(3, 20)->asString();
        $itemId = $this->input->get("ItemID")->checkStringLength(3, 10)->asInt();
        $transactionType = $this->input->get("Type")->asString();
        $PaymentGross = $this->input->get("PaymentGross")->asInt();
        $checks = [
            "PaymentGross" => $PaymentGross,
            "payerKey" => $payerKey,
            "payerName" => $payerName,
            "receiverKey" => $receiverKey,
            "receiverName" => $receiverName,
            "transactionId" => $transactionId,
            "itemId" => $itemId,
        ];

        if (in_array(null, $checks) == true) {
            foreach ($checks as $key => $value) {
                if ($value != null) {
                    continue;
                }
                $this->failed("ANS not accepted missing value for: " . $key);
                break;
            }
            return;
        }
        if ($transactionType != "Purchase") {
            $this->failed("ANS redelivery so ignored");
            return;
        }
        $marketplace = new Marketplacecoupons();
        $marketplace->loadByListingid($itemId);
        if ($marketplace->isLoaded() == false) {
            $this->failed("not a tracked listing");
            return;
        }
        if ($marketplace->_Cost != $PaymentGross) {
            $this->failed("amount paid does not match expected to process");
            return;
        }

        $vaildateHash = sha1($_SERVER['QUERY_STRING'] . $this->siteConfig->getSlConfig()->_AnsSalt);
        if ($checkHash != $vaildateHash) {
            $this->failed("Unable to vaildate");
            return;
        }
        if (count(explode(" ", $payerName)) == 1) {
            $payerName = $payerName . " Resident";
        }
        if (count(explode(" ", $receiverName)) == 1) {
            $receiverName = $receiverName . " Resident";
        }
        $marketplace->_Claims = $marketplace->_Claims + 1;
        $marketplace->_LastClaim = time();
        $marketplace->updateEntry();
        $avatarHelper = new AvatarHelper();
        if ($avatarHelper->loadOrCreate($payerKey, $payerName) == false) {
            $this->failed("Unable to create/load payer avatar");
            return;
        }
        $payerAv = $avatarHelper->getAvatar();
        if ($avatarHelper->loadOrCreate($receiverKey, $receiverName) == false) {
            $this->failed("Unable to create/load receiver avatar");
            return;
        }
        $receiverAv = $avatarHelper->getAvatar();

        if (
            $this->createTransaction(
                $transactionId,
                $PaymentGross,
                $marketplace->_Credit,
                $payerAv,
                $receiverAv
            ) == false
        ) {
            return;
        }
        if ($this->topupNotice($receiverAv, $marketplace->_Credit) == false) {
            $this->failed("Unable to send message");
            return;
        }
        if ($receiverAv->_Id != $payerAv->_Id) {
            if ($this->thankAvatar($payerAv, $receiverAv, $marketplace->_Credit) == false) {
                $this->failed("Unable to send message");
                return;
            }
        }
        $receiverAv->_Credits = $receiverAv->_Credits + $marketplace->_Credit;
        if ($receiverAv->updateEntry()->status == false) {
            $this->failed("Unable to update credits");
            return;
        }
        $this->ok("proceed ans");
    }

    protected function createTransaction(
        string $transactionid,
        int $amount,
        int $credits,
        Avatar $payerAv,
        Avatar $receiverAv
    ): bool {
        $transaction = new Transactions();
        $uid_transaction = $transaction->createUID("transactionUid", 8);
        if ($uid_transaction->status == false) {
            $this->failed("Unable to create transaction uid");
            return false;
        }
        $transaction = new Transactions();
        $transaction->_AvatarLink = $payerAv->_Id;
        $transaction->_Amount = $amount;
        $transaction->_Unixtime = time();
        $transaction->_TransactionUid = $uid_transaction->uid;
        $transaction->_SLtransactionUUID = $transactionid;
        $transaction->_ViaMarketplace = true;
        $transaction->_Notes = "Coupon L$" . $amount . " added L$" . $credits;
        if ($receiverAv->_Id != $payerAv->_Id) {
            $transaction->_TargetAvatar = $receiverAv->_Id;
        }
        if ($transaction->createEntry()->status == false) {
            $this->setSwapTag("message", "Unable to create transaction");
            return false;
        }
        return true;
    }

    protected function topupNotice(Avatar $to, int $credit): bool
    {
        $bot_helper = new BotHelper();
        $sendmessage = "Hi there " . $to->_AvatarName . " a credit of L$" . $credit
            . " has been added to your account!";
        $sendMessage_status = $bot_helper->sendMessage(
            $to,
            $sendmessage,
        );
        if ($sendMessage_status->status == false) {
            $this->failed("Unable to put mail into outbox");
            return false;
        }
        return true;
    }

    protected function thankAvatar(Avatar $from, Avatar $to, int $credit): bool
    {
        $bot_helper = new BotHelper();
        $sendmessage = "Hi there " . $from->_AvatarName . " thank you for adding L$" . $credit
            . " to the account for " . $to->_AvatarName;
        $sendMessage_status = $bot_helper->sendMessage(
            $from,
            $sendmessage,
        );
        if ($sendMessage_status->status == false) {
            $this->failed("Unable to put mail into outbox");
            return false;
        }
        return true;
    }
}
