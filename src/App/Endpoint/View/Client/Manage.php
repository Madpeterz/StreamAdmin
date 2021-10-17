<?php

namespace App\Endpoint\View\Client;

use App\Helpers\ServerApi\ServerApiHelper;
use App\R7\Model\Avatar;
use App\R7\Set\AvatarSet;
use App\R7\Model\Package;
use App\R7\Set\RegionSet;
use App\R7\Model\Rental;
use App\R7\Set\ResellerSet;
use App\R7\Model\Stream;
use App\R7\Set\NoticeSet;
use App\R7\Set\PackageSet;
use App\R7\Set\RentalnoticeptoutSet;
use App\R7\Set\RentalSet;
use App\R7\Set\ServerSet;
use App\R7\Set\StreamSet;
use App\Template\Form;
use App\Template\Grid;
use App\R7\Set\TransactionsSet;
use App\Template\PagedInfo;

class Manage extends View
{
    protected array $pages = [];
    protected Rental $rental;
    protected Avatar $avatar;
    protected ServerSet $servers;

    protected function loadServers(): bool
    {
        $this->servers = new ServerSet();
        return $this->servers->loadAll()["status"];
    }

    public function process(): void
    {
        if ($this->loadServers() == false) {
            $this->output->redirect("client?bubblemessage=unable to load servers "
            . $this->page . "&bubbletype=danger");
            return;
        }
        $this->output->addSwapTagString("html_title", "~ Manage");
        $this->output->addSwapTagString("page_title", "Editing client");

        $this->rental = new Rental();
        if ($this->rental->loadByField("rentalUid", $this->page) == false) {
            $this->output->redirect("client?bubblemessage=unable to find client "
            . $this->page . "&bubbletype=warning");
            return;
        }

        $stream = new Stream();
        $stream->loadID($this->rental->getStreamLink());
        $this->setSwapTag("page_actions", "");
        if ($stream->getId() > 0) {
            $this->output->addSwapTagString(
                "page_actions",
                "<a href='[[url_base]]Stream/Manage/" . $stream->getStreamUid() . "'>"
                . "<button type='button' class='btn btn-info'>View Stream</button></a>"
            );
        }


        $this->output->addSwapTagString("page_actions", "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"
        . "<button type='button' data-actiontitle='Revoke client " . $this->page . "' data-actiontext='End now' data-actionmessage='This will end the rental 
        with no refund!' data-targetendpoint='[[url_base]]client/revoke/" . $this->page . "' 
        class='btn btn-danger confirmDialog'>Revoke</button></a>");

        $paged_info = new PagedInfo();
        $this->clientManageForm();
        $this->pages["Other rentals"] = $this->clientAllStreamsTable($this->avatar);
        $this->clientMessageForm();
        $this->clientApiActions();
        $this->clientTransactions();
        $this->clientOptOut();
        $this->setSwapTag("page_content", $paged_info->render($this->pages));
    }

    public function clientAllStreamsTable(Avatar $targetAvatar): string
    {
        $rentalSet = new RentalSet();
        $rentalSet->loadByAvatarLink($targetAvatar->getId());

        $streamSet = new StreamSet();
        $streamSet->loadByValues($rentalSet->getUniqueArray("streamLink"));

        $packageSet = new PackageSet();
        $packageSet->loadByValues($streamSet->getUniqueArray("packageLink"));

        $tableHead = ["Server","Port","Package","Timeleft","Started","Renewals","Client page","Stream page"];
        $tableBody = [];
        foreach ($rentalSet as $rental) {
            $entry = [];
            $stream = $streamSet->getObjectByID($rental->getStreamLink());
            $server = $this->servers->getObjectByID($stream->getServerLink());
            $package = $packageSet->getObjectByID($stream->getPackageLink());
            $entry[] = "<a href=\"[[url_base]]stream/onserver/"
            . $server->getId() . "\">" . $server->getDomain() . "</a>";
            $entry[] = $stream->getPort();
            $entry[] = "<a href=\"package/manage/" . $package->getPackageUid() . "\">" . $package->getName() . "</a>";
            $entry[] = timeleftHoursAndDays($rental->getExpireUnixtime(), false, "Expired");
            $entry[] = date('d/m/Y @ G:i:s', $rental->getStartUnixtime());
            $entry[] = $rental->getRenewals();
            $entry[] = "<a href=\"[[url_base]]client/manage/"
            . $rental->getRentalUid() . "\">" . $rental->getRentalUid() . "</a>";
            $entry[] = "<a href=\"[[url_base]]stream/manage/"
            . $stream->getStreamUid() . "\">" . $stream->getStreamUid() . "</a>";
            $tableBody[] = $entry;
        }

        return $this->renderTable($tableHead, $tableBody);
    }

    protected function clientManageForm(): void
    {
        $this->avatar = new Avatar();
        $this->avatar->loadID($this->rental->getAvatarLink());
        $this->output->addSwapTagString("page_title", ": " . $this->rental->getRentalUid() . " "
        . "[" . $this->avatar->getAvatarName() . "]");
        $form = new Form();
        $form->target("client/update/" . $this->page . "");
        $form->required(true);
        $form->col(6);
            $form->group("Timeleft: " . timeleftHoursAndDays($this->rental->getExpireUnixtime()) . "");
            $form->directAdd("<sub>" . date('l jS \of F Y h:i:s A', $this->rental->getExpireUnixtime()) . "</sub>"
            . "<br/><br/>");
            $form->numberInput("adjustment_days", "Adjustment [Days]", 0, 3, "Max 999");
            $form->numberInput("adjustment_hours", "Adjustment [Hours]", 0, 2, "Max 23");
            $form->select("adjustment_dir", "Adjustment (Type)", false, [false => "Remove",true => "Add"]);
        $form->col(6);
            $form->group("Transfer");
            $form->textInput(
                "transfer_avataruid",
                "Avatar UID <a data-toggle=\"modal\" data-target=\"#AvatarPicker\" "
                . "href=\"#\" target=\"_blank\">Find</a>",
                8,
                "",
                "Avatar UID (Not SL UUID)"
            );
        $form->split();
        $form->col(6);
            $form->group(" ");
            $form->textarea(
                "message",
                "Message",
                9999,
                $this->rental->getMessage(),
                "Any rental with a message will not be listed on the Fast removal system! Max length 9999"
            );
        $form->col(6);
            $form->group("API flags");
            $form->select(
                "apiAllowSuspend",
                "Allow auto suspend",
                $this->rental->getApiAllowAutoSuspend(),
                $this->yesNo
            );

        $this->pages["Client"] = $form->render("Update", "primary");
    }
    protected function clientMessageForm(): void
    {
        $form = new Form();
        $form->target("client/message/" . $this->page . "");
        $form->required(true);
        $form->textarea(
            "mail",
            "",
            800,
            "",
            "Send a IM to the selected avatar"
        );
        $this->pages["Mail"] = $form->render("Send", "success");
    }
    protected function clientOptOut(): void
    {
        $noticeLevels = new NoticeSet();
        $whereConfig = [
            "fields" => ["id"],
            "values" => [10],
            "matches" => ["!="],
        ];
        $load = $noticeLevels->loadWithConfig($whereConfig);
        if ($load["status"] == false) {
            return;
        }
        $client_opt_out = new RentalnoticeptoutSet();
        $client_opt_out->loadByRentalLink($this->rental->getId());
        $opt_out_notice_ids = $client_opt_out->getUniqueArray("noticeLink");
        $table_head = ["Notice level","Status"];
        $table_body = [];
        foreach ($noticeLevels as $noticeLevel) {
            $entry = [];
            $entry[] = $noticeLevel->getName();
            $enabled = in_array($noticeLevel->getId(), $opt_out_notice_ids);
            $entry[] = $this->makeOptoutButton($noticeLevel->getId(), $enabled);
            $table_body[] = $entry;
        }
        $form = new Form();
        $form->target("Client/NoticeOptout/" . $this->page . "");
        $form->required(true);
        $form->col(12);
        $form->directAdd($this->renderTable($table_head, $table_body));
        $this->pages["Notice opt-out"] = $form->render("Update opt-out", "info");
    }
    protected function makeOptoutButton(
        int $noticeID,
        bool $enabled
    ): string {
        if ($enabled == true) {
            return '
            <div class="btn-group btn-group-toggle" data-toggle="buttons">
            <label class="btn btn-outline-danger active">
                <input type="radio" checked value="0" name="remove-optout-'
                . $noticeID . '" autocomplete="off"> Disable messages
            </label>
            <label class="btn btn-outline-secondary">
                <input type="radio" value="1" name="remove-optout-'
                . $noticeID . '" autocomplete="off"> Restore
            </label>
            </div>';
        }
        return '
        <div class="btn-group btn-group-toggle" data-toggle="buttons">
        <label class="btn btn-outline-danger">
            <input type="radio" value="1" name="add-optout-' . $noticeID . '" autocomplete="off"> Disable messages
        </label>
        <label class="btn btn-outline-secondary" active>
            <input type="radio" checked value="0" name="add-optout-' . $noticeID . '" autocomplete="off"> Normal
        </label>
        </div>';
    }

    protected function clientTransactions(): void
    {
        $where_config = [
            "fields" => ["streamLink","unixtime"],
            "values" => [$this->rental->getStreamLink(),$this->rental->getStartUnixtime()],
            "types" => ["i","i"],
            "matches" => ["=",">="],
        ];
        $order_by = ["ordering_enabled" => true,"order_field" => "unixtime","order_dir" => "DESC"];
        $transactions_set = new TransactionsSet();
        $transactions_set->loadWithConfig($where_config, $order_by);

        $reseller_set = new ResellerSet();
        $region_set = new RegionSet();
        $avatar_set = new AvatarSet();
        $region_set->loadByValues($transactions_set->getAllByField("regionLink"));
        $reseller_set->loadByValues($transactions_set->getAllByField("resellerLink"));
        $avatar_set->loadByValues(
            array_merge(
                $transactions_set->getAllByField("avatarLink"),
                $reseller_set->getAllByField("avatarLink")
            ),
            "id",
            "i",
            false
        );
        $table_head = ["Transaction UID","Avatar","Reseller","Region","Amount","Datetime"];
        $table_body = [];
        foreach ($transactions_set as $transaction) {
            $avatar = $avatar_set->getObjectByID($transaction->getAvatarLink());
            $region = $region_set->getObjectByID($transaction->getRegionLink());
            $reseller = $reseller_set->getObjectByID($transaction->getResellerLink());
            $reseller_av = $avatar_set->getObjectByID($reseller->getAvatarLink());
            $entry = [];
            //$entry[] = $transaction->getId();
            $entry[] = $transaction->getTransactionUid();
            $entry[] = $avatar->getAvatarName();
            $entry[] = $reseller_av->getAvatarName();
            $entry[] = $region->getName();
            $entry[] = $transaction->getAmount();
            $entry[]  = date('l jS \of F Y h:i:s A', $transaction->getUnixtime());
            $table_body[] = $entry;
        }
        $this->pages["Transactions"] = $this->renderTable($table_head, $table_body);
    }
    protected function clientApiActions(): void
    {
        $stream = new Stream();
        $package = new Package();
        if ($stream->loadID($this->rental->getStreamLink()) == false) {
            return;
        }
        $server = $this->servers->getObjectByID($stream->getServerLink());
        if ($server == null) {
            return;
        }
        if ($server->getApiLink() < 2) {
            return;
        }
        if ($package->loadID($stream->getPackageLink()) == false) {
            return;
        }
        $serverapi_helper = new ServerApiHelper();
        $serverapi_helper->forceSetRental($this->rental);
        $serverapi_helper->forceSetServer($server);
        $serverapi_helper->forceSetPackage($package);
        $serverapi_helper->forceSetStream($stream, false);
        $mygrid = new Grid();

        $api_actions = [
            "Stop" => "danger",
            "Start" => "success",
            "AutodjNext" => "info",
            "AutodjToggle" => "secondary",
            "CustomizeUsername" => "warning",
            "ResetPasswords" => "warning",
            "EnableAccount" => "success",
            "DisableAccount" => "danger",
            "ListDjs" => "info",
            "PurgeDjs" => "danger",
        ];
        foreach ($api_actions as $key => $value) {
            if ($serverapi_helper->callableAction("api" . $key) == true) {
                $form = new Form();
                $form->target("client/api/" . $this->page . "/" . $key);
                $buttontext = str_replace("_", " ", ucfirst($key));
                $mygrid->addContent($form->render($buttontext, $value, true), 4);
            } else {
                $mygrid->addContent($serverapi_helper->getMessage(), 4);
            }
        }
        $mygrid->addContent("<hr/>", 12);
        if ($serverapi_helper->callableAction("apiSetPasswords") == true) {
            $form = new Form();
            $form->target("client/api/" . $this->page . "/SetPasswords");
            $form->group("API force set passwords");
            $form->textInput(
                "set_dj_password",
                "Set DJ password",
                6,
                $stream->getDjPassword(),
                "DJ/Stream password"
            );
            $form->textInput(
                "set_admin_password",
                "Set Admin password",
                6,
                $stream->getAdminPassword(),
                "Admin password"
            );
            $mygrid->addContent($form->render("Set passwords", "warning", true), 6);
        }
        $this->pages["API"] = $mygrid->getOutput();
        $avname = explode(" ", strtolower($this->avatar->getAvatarName()));
        if ($serverapi_helper->callableAction("apiCustomizeUsername") == true) {
            $this->pages["API"] .= "<br/>customize username changes the admin username for the stream following"
            . " this ruleset<br/><ol>
            <li>Firstname eg:\"" . $avname[0] . "\"</li>
            <li>Firstname 2 letters of last name:\"" . $avname[0] . "_" . substr($avname[1], 0, 2) . "\"</li>
            <li>Firstname Port: \"" . $avname[0] . "_" . $stream->getPort() . "\"</li>
            <li>Firstname Port Bitrate: \"" . $avname[0] . "_" . $stream->getPort() . "_"
            . $package->getBitrate() . "\"</li>
            <li>Firstname Port ServerID: \"" . $avname[0] . "_" . $stream->getPort() . "_" . $server->getId() . "\"</li>
            <li>Firstname RentalUID: \"" . $avname[0] . "_" . $this->rental->getRentalUid() . "\"</li>
            </ol>";
        }
    }
}
