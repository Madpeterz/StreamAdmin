<?php

namespace App\Endpoint\View\Client;

use App\Models\Avatar;
use App\Models\Set\AvatarSet;
use App\Models\Rental;
use App\Models\Set\NoticeSet;
use App\Models\Set\RentalnoticeptoutSet;
use App\Models\Set\RentalSet;
use App\Models\Set\ServerSet;
use YAPF\Bootstrap\Template\Form;
use App\Models\Set\TransactionsSet;
use YAPF\Bootstrap\Template\PagedInfo as TemplatePagedInfo;

class Manage extends View
{
    protected array $pages = [];
    protected Rental $rental;
    protected Avatar $avatar;
    protected ServerSet $servers;

    protected function loadServers(): bool
    {
        $this->servers = new ServerSet();
        return $this->servers->loadAll()->status;
    }

    public function process(): void
    {
        if ($this->loadServers() == false) {
            $this->output->redirect("client?bubblemessage=unable to load servers "
            . $this->siteConfig->getPage() . "&bubbletype=danger");
            return;
        }
        $this->output->addSwapTagString("html_title", "~ Manage");
        $this->output->addSwapTagString("page_title", "Editing client");

        $this->rental = new Rental();
        if ($this->rental->loadByRentalUid($this->siteConfig->getPage())->status == false) {
            $this->output->redirect("client?bubblemessage=unable to find client "
            . $this->siteConfig->getPage() . "&bubbletype=warning");
            return;
        }

        $stream = $this->rental->relatedStream()->getFirst();
        if ($stream === null) {
            $this->output->redirect("client?bubblemessage=unable to load stream "
            . $this->siteConfig->getPage() . "&bubbletype=danger");
            return;
        }
        $this->setSwapTag("page_actions", "");
        if ($stream->_Id > 0) {
            $this->output->addSwapTagString(
                "page_actions",
                "<button type='button' data-rentaluid=\"" . $this->rental->_RentalUid . "\" data-toggle=\"modal\" 
                data-target=\"#NotecardModal\" class='btn btn-sm btn-success'>Notecard</button>"
            );

            $this->output->addSwapTagString(
                "page_actions",
                "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='[[SITE_URL]]Stream/Manage/" . $stream->_StreamUid . "'>"
                . "<button type='button' class='btn btn-outline-light'>View Stream</button></a>"
            );
        }

        $hours_remaining = 0;
        $time_remaining = $this->rental->_ExpireUnixtime - time();
        if ($time_remaining < 0) {
            $time_remaining = 0;
        }
        while ($time_remaining > $this->siteConfig->unixtimeHour()) {
            $hours_remaining++;
            $time_remaining -= $this->siteConfig->unixtimeHour();
        }
        $package = $this->rental->relatedPackage()->getFirst();
        $est_refund_value_per_hour = ($package->_Cost / $package->_Days) / 24;
        $est_refund = round($hours_remaining * $est_refund_value_per_hour);


        $this->output->addSwapTagString("page_actions", "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"
        . "<button type='button' data-actiontitle='Revoke client " . $this->siteConfig->getPage() . "
        ' data-actiontext='End now' data-actionmessage='This will end the rental 
        with no refund!<br/> Est L$ value: " . $est_refund . "
        ' data-targetendpoint='[[SITE_URL]]client/revoke/" . $this->siteConfig->getPage() . "' 
        class='btn btn-danger confirmDialog'>Revoke</button></a>");

        $paged_info = new TemplatePagedInfo();
        $this->clientManageForm();
        $this->pages["Other rentals"] = $this->clientAllStreamsTable($this->avatar, $this->rental->_RentalUid);
        $this->clientMessageForm();
        $this->clientTransactions();
        $this->clientOptOut();
        $this->setSwapTag("page_content", $paged_info->render($this->pages));
    }

    public function clientAllStreamsTable(Avatar $targetAvatar, ?string $ignoreRentalUid = null): string
    {
        $rentalSet = $targetAvatar->relatedRental();
        $streamSet = $rentalSet->relatedStream();
        $packageSet = $streamSet->relatedPackage();

        $tableHead = ["Server","Port","Package","Timeleft","Started","Renewals","Client page","Stream page"];
        $tableBody = [];
        foreach ($rentalSet as $rental) {
            if ($rental->_RentalUid == $ignoreRentalUid) {
                continue;
            }
            $entry = [];
            $stream = $streamSet->getObjectByID($rental->_StreamLink);
            $server = $this->servers->getObjectByID($stream->_ServerLink);
            $package = $packageSet->getObjectByID($stream->_PackageLink);
            $entry[] = "<a href=\"[[SITE_URL]]stream/onserver/"
            . $server->_Id . "\">" . $server->_Domain . "</a>";
            $entry[] = $stream->_Port;
            $entry[] = "<a href=\"package/manage/" . $package->_PackageUid . "\">" . $package->_Name . "</a>";
            $entry[] = $this->timeRemainingHumanReadable($rental->_ExpireUnixtime, false, "Expired");
            $entry[] = date('d/m/Y @ G:i:s', $rental->_StartUnixtime);
            $entry[] = $rental->_Renewals;
            $entry[] = "<a href=\"[[SITE_URL]]client/manage/"
            . $rental->_RentalUid . "\">" . $rental->_RentalUid . "</a>";
            $entry[] = "<a href=\"[[SITE_URL]]stream/manage/"
            . $stream->_StreamUid . "\">" . $stream->_StreamUid . "</a>";
            $tableBody[] = $entry;
        }

        return $this->renderTable($tableHead, $tableBody);
    }

    protected function clientManageForm(): void
    {
        $this->avatar = new Avatar();
        $this->avatar->loadID($this->rental->_AvatarLink);
        $this->output->addSwapTagString("page_title", ": " . $this->rental->_RentalUid . " "
        . "[" . $this->avatar->_AvatarName . "]");
        $form = new Form();
        $form->target("client/update/" . $this->siteConfig->getPage() . "");
        $form->required(true);
        $form->col(6);
            $form->group("Timeleft: " . $this->timeRemainingHumanReadable($this->rental->_ExpireUnixtime) . "");
            $form->directAdd("<sub>" . date('l jS \of F Y h:i:s A', $this->rental->_ExpireUnixtime) . "</sub>"
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
        $form->col(12);
            $form->group(" ");
            $form->textarea(
                "message",
                "Message",
                9999,
                $this->rental->_Message,
                "Any rental with a message will not be listed on the Fast removal system! Max length 9999"
            );
            $this->pages["Client"] = $form->render("Update", "primary");
    }
    protected function clientMessageForm(): void
    {
        $form = new Form();
        $form->target("client/message/" . $this->siteConfig->getPage() . "");
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
        if ($load->status == false) {
            return;
        }
        $client_opt_out = new RentalnoticeptoutSet();
        $client_opt_out->loadByRentalLink($this->rental->_Id);
        $opt_out_notice_ids = $client_opt_out->uniqueNoticeLinks();
        $table_head = ["Notice level","Status"];
        $table_body = [];
        foreach ($noticeLevels as $noticeLevel) {
            $entry = [];
            $entry[] = $noticeLevel->_Name;
            $enabled = in_array($noticeLevel->_Id, $opt_out_notice_ids);
            $entry[] = $this->makeOptoutButton($noticeLevel->_Id, $enabled);
            $table_body[] = $entry;
        }
        $form = new Form();
        $form->target("Client/NoticeOptout/" . $this->siteConfig->getPage() . "");
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
            <label class="btn btn-outline-secondary active">
                <input type="radio" checked value="0" name="remove-optout-'
                . $noticeID . '" autocomplete="off"> Skipped
            </label>
            <label class="btn btn-outline-success">
                <input type="radio" value="1" name="remove-optout-'
                . $noticeID . '" autocomplete="off"> Restore
            </label>
            </div>';
        }
        return '
        <div class="btn-group btn-group-toggle" data-toggle="buttons">
        <label class="btn btn-outline-danger">
            <input type="radio" value="1" name="add-optout-' . $noticeID . '" autocomplete="off"> Skip
        </label>
        <label class="btn btn-outline-info" active>
            <input type="radio" checked value="0" name="add-optout-' . $noticeID . '" autocomplete="off"> Normal
        </label>
        </div>';
    }

    protected function clientTransactions(): void
    {
        $where_config = [
            "fields" => ["streamLink","unixtime"],
            "values" => [$this->rental->_StreamLink,$this->rental->_StartUnixtime],
            "types" => ["i","i"],
            "matches" => ["=",">="],
        ];
        $order_by = ["ordering_enabled" => true,"order_field" => "unixtime","order_dir" => "DESC"];
        $transactions_set = new TransactionsSet();
        $transactions_set->loadWithConfig($where_config, $order_by);

        $reseller_set = $transactions_set->relatedReseller();
        $region_set = $transactions_set->relatedRegion();
        $avatar_set = new AvatarSet();
        $avatar_set->loadFromIds(
            array_merge(
                $transactions_set->getAllByField("avatarLink"),
                $reseller_set->getAllByField("avatarLink")
            )
        );
        $table_head = ["Transaction UID","Avatar","Reseller","Region","Amount","Datetime"];
        $table_body = [];
        foreach ($transactions_set as $transaction) {
            $avatar = $avatar_set->getObjectByID($transaction->_AvatarLink);
            $region = $region_set->getObjectByID($transaction->_RegionLink);
            $reseller = $reseller_set->getObjectByID($transaction->_ResellerLink);
            $reseller_av = $avatar_set->getObjectByID($reseller->_AvatarLink);
            $entry = [];
            //$entry[] = $transaction->getId();
            $entry[] = $transaction->_TransactionUid;
            $entry[] = $avatar->_AvatarName;
            $entry[] = $reseller_av->_AvatarName;
            $entry[] = $region->_Name;
            $entry[] = $transaction->_Amount;
            $entry[]  = date('l jS \of F Y h:i:s A', $transaction->_Unixtime);
            $table_body[] = $entry;
        }
        $this->pages["Transactions"] = $this->renderTable($table_head, $table_body);
    }
}
