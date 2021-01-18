<?php

namespace App\Endpoints\SecondLifeHudApi\Rentals;

use App\Endpoints\SecondLifeApi\Renew\Renewnow;
use App\Helpers\BotHelper;
use App\Helpers\RegionHelper;
use App\Models\Avatar;
use App\Models\Botconfig;
use App\Models\Package;
use App\Models\Rental;
use App\Models\Server;
use App\Models\Stream;
use App\Template\SecondlifeAjax;
use App\Helpers\ResellerHelper;
use App\Helpers\SwapablesHelper;
use YAPF\InputFilter\InputFilter;

class Topup extends SecondlifeAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $rental_uid = $input->postFilter("uid");
        $amount = $input->postFilter("amount", "integer");
        $transactionid = $input->postFilter("transactionid", "uuid");
        $tidhash = $input->postFilter("tidhash");
        $tidtime = $input->postFilter("tidtime", "integer");
        $regionname = $input->postFilter("regionname");
        $fasttest = [$amount,$rental_uid,$transactionid,$tidhash,$tidtime,$regionname];
        if (in_array(null, $fasttest) == true) {
            $this->setSwapTag("message", "One or more values passed are not set correctly");
            return;
        }
        $region_helper = new RegionHelper();
        $get_region_status = $region_helper->loadOrCreate($regionname);
        if ($get_region_status == false) {
            $this->setSwapTag("message", "Unable to find or setup new region for transaction");
            return;
        }
        $rental = new Rental();
        if ($rental->loadByField("rental_uid", $rental_uid) == false) {
            $this->setSwapTag("message", "Unable to find rental");
            return;
        }
        if ($rental->getAvatarlink() != $this->object_owner_avatar->getId()) {
            $this->setSwapTag("message", "Unable to process topup");
            return;
        }
        $stream = new Stream();
        if ($stream->loadID($rental->getStreamlink()) == false) {
            $this->setSwapTag("message", "Unable to load stream");
            return;
        }
        $server = new Server();
        if ($server->loadID($stream->getServerlink()) == false) {
            $this->setSwapTag("message", "Unable to load server");
            return;
        }
        $package = new Package();
        if ($package->loadID($rental->getPackagelink()) == false) {
            $this->setSwapTag("message", "Unable to load package");
            return;
        }
        if ($amount != $package->getCost()) {
            $this->setSwapTag("message", "Transaction rejected");
            return;
        }
        $bits = [
            $rental_uid,
            $amount,
            $transactionid,
            $tidtime,
            $this->object_owner_avatar->getAvataruuid(),
            $this->slconfig->getPubliclinkcode(),
            $rental->getExpireunixtime(),
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
        if ($avatar_system->loadID($this->slconfig->getOwner_av()) == false) {
            $this->setSwapTag("message", "Unable to load system owner avatar");
            return;
        }
        $reseller_helper = new ResellerHelper();
        if ($reseller_helper->loadOrCreate($avatar_system->getId(), true, 100) == false) {
            $this->setSwapTag("message", "Unable to load system owner reseller acccount");
            return;
        }

        $_POST["rental_uid"] = $rental_uid;
        $_POST["avataruuid"] = $this->object_owner_avatar->getAvataruuid();
        $_POST["avatarname"] = $this->object_owner_avatar->getAvatarname();
        $_POST["amountpaid"] = $amount;

        $apiobj = new Renewnow();
        $apiobj->setReseller($reseller_helper->getReseller());
        $apiobj->setOwnerOverride(true);
        $apiobj->process();
        $this->output = $apiobj->getOutputObject();
        if ($this->output->getSwapTagString("status") == "true") {
            $bot_helper = new BotHelper();
            $swapables_helper = new SwapablesHelper();

            $botconfig = new Botconfig();
            $botconfig->loadID(1);

            $botavatar = new Avatar();
            $botavatar->loadID($botconfig->getAvatarlink());

            $sendmessage = $swapables_helper->get_swapped_text(
                "= Remote transaction notice =[[NL]] User: [[AVATAR_FULLNAME]] has topped up L$"
                . $amount . " [[NL]] Rental: "
                . $rental->getRental_uid() . " on port: "
                . $stream->getPort() . " [[NL]] transaction ID:"
                . $transactionid . "",
                $this->object_owner_avatar,
                $rental,
                $package,
                $server,
                $stream
            );
            $bot_helper->send_message($botconfig, $botavatar, $avatar_system, $sendmessage, true);
        }
    }
}
