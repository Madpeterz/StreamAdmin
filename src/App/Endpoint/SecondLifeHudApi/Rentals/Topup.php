<?php

namespace App\Endpoint\SecondLifeHudApi\Rentals;

use App\Endpoint\SecondLifeApi\Renew\Renewnow;
use App\Helpers\BotHelper;
use App\Helpers\RegionHelper;
use App\R7\Model\Avatar;
use App\R7\Model\Botconfig;
use App\R7\Model\Package;
use App\R7\Model\Rental;
use App\R7\Model\Server;
use App\R7\Model\Stream;
use App\Template\SecondlifeHudAjax;
use App\Helpers\ResellerHelper;
use App\Helpers\SwapablesHelper;
use YAPF\InputFilter\InputFilter;

class Topup extends SecondlifeHudAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $rentalUid = $input->postFilter("rentalUid");
        $amount = $input->postFilter("amount", "integer");
        $transactionid = $input->postFilter("transactionid", "uuid");
        $tidhash = $input->postFilter("tidhash");
        $tidtime = $input->postFilter("tidtime", "integer");
        $fasttest = [$amount,$rentalUid,$transactionid,$tidhash,$tidtime];
        if (in_array(null, $fasttest) == true) {
            $this->setSwapTag("message", "One or more values passed are not set correctly");
            return;
        }
        $rental = new Rental();
        if ($rental->loadByField("rentalUid", $rentalUid) == false) {
            $this->setSwapTag("message", "Unable to find rental");
            return;
        }
        if ($rental->getAvatarLink() != $this->Object_OwnerAvatar->getId()) {
            $this->setSwapTag("message", "Unable to process topup");
            return;
        }
        $stream = new Stream();
        if ($stream->loadID($rental->getStreamLink()) == false) {
            $this->setSwapTag("message", "Unable to load stream");
            return;
        }
        $server = new Server();
        if ($server->loadID($stream->getServerLink()) == false) {
            $this->setSwapTag("message", "Unable to load server");
            return;
        }
        $package = new Package();
        if ($package->loadID($rental->getPackageLink()) == false) {
            $this->setSwapTag("message", "Unable to load package");
            return;
        }
        if ($amount != $package->getCost()) {
            $this->setSwapTag("message", "Transaction rejected");
            return;
        }
        $bits = [
            $rentalUid,
            $amount,
            $transactionid,
            $tidtime,
            $this->Object_OwnerAvatar->getAvatarUUID(),
            $this->slconfig->getHudLinkCode(),
            $rental->getExpireUnixtime(),
        ];
        $raw = implode("", $bits);
        $tidhashcheck = sha1($raw);
        if ($tidhashcheck != $tidhash) {
            $this->setSwapTag("message", "Transaction rejected");
            return;
        }
        $dif = time() - $tidtime;
        if (($dif > 120) || ($dif < -120)) {
            $this->setSwapTag("message", "Transaction rejected");
            return;
        }
        $avatar_system = new Avatar();
        if ($avatar_system->loadID($this->slconfig->getOwnerAvatarLink()) == false) {
            $this->setSwapTag("message", "Unable to load system owner avatar");
            return;
        }
        $reseller_helper = new ResellerHelper();
        if ($reseller_helper->loadOrCreate($avatar_system->getId(), true, 100) == false) {
            $this->setSwapTag("message", "Unable to load system owner reseller acccount");
            return;
        }
        if ($this->region == null) {
            $this->setSwapTag("message", "Region is null");
            return;
        }

        $_POST["rentalUid"] = $rentalUid;
        $_POST["avatarUUID"] = $this->Object_OwnerAvatar->getAvatarUUID();
        $_POST["avatarName"] = $this->Object_OwnerAvatar->getAvatarName();
        $_POST["amountpaid"] = $amount;

        $apiobj = new Renewnow();
        $apiobj->setReseller($reseller_helper->getReseller());
        $apiobj->setOwnerOverride(true);
        $apiobj->setRegion($this->region);
        $apiobj->process();
        $this->output = $apiobj->getOutputObject();
        if ($this->output->getSwapTagString("status") == "true") {
            $bot_helper = new BotHelper();
            $swapables_helper = new SwapablesHelper();

            $botconfig = new Botconfig();
            $botconfig->loadID(1);

            $botavatar = new Avatar();
            $botavatar->loadID($botconfig->getAvatarLink());

            $sendmessage = $swapables_helper->getSwappedText(
                "= Remote transaction notice =[[NL]] User: [[AVATAR_FULLNAME]] has topped up L$"
                . $amount . " [[NL]] Rental: "
                . $rental->getRentalUid() . " on port: "
                . $stream->getPort() . " [[NL]] transaction ID:"
                . $transactionid . "",
                $this->Object_OwnerAvatar,
                $rental,
                $package,
                $server,
                $stream
            );
            $bot_helper->sendMessage($botconfig, $botavatar, $avatar_system, $sendmessage, true);
        }
    }
}
