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
        if ($this->siteConfig->getSlConfig()->getEnableCoupons() == false) {
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
        if ($marketplace->getCost() != $PaymentGross) {
            $this->failed("amount paid does not match expected to process");
            return;
        }

        $vaildateHash = sha1($_SERVER['QUERY_STRING'] . $this->siteConfig->getSlConfig()->getAnsSalt());
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
        $marketplace->setClaims($marketplace->getClaims() + 1);
        $marketplace->setLastClaim(time());
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
                $marketplace->getCredit(),
                $payerAv,
                $receiverAv
            ) == false
        ) {
            return;
        }
        if ($this->topupNotice($receiverAv, $marketplace->getCredit()) == false) {
            $this->failed("Unable to send message");
            return;
        }
        if ($receiverAv->getId() != $payerAv->getId()) {
            if ($this->thankAvatar($payerAv, $receiverAv, $marketplace->getCredit()) == false) {
                $this->failed("Unable to send message");
                return;
            }
        }
        $receiverAv->setCredits($receiverAv->getCredits() + $marketplace->getCredit());
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
        $transaction->setAvatarLink($payerAv->getId());
        $transaction->setAmount($amount);
        $transaction->setUnixtime(time());
        $transaction->setTransactionUid($uid_transaction->uid);
        $transaction->setSLtransactionUUID($transactionid);
        $transaction->setViaMarketplace(true);
        $transaction->setNotes("Coupon L$" . $amount . " added L$" . $credits);
        if ($receiverAv->getId() != $payerAv->getId()) {
            $transaction->setTargetAvatar($receiverAv->getId());
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
        $sendmessage = "Hi there " . $to->getAvatarName() . " a credit of L$" . $credit
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
        $sendmessage = "Hi there " . $from->getAvatarName() . " thank you for adding L$" . $credit
            . " to the account for " . $to->getAvatarName();
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
