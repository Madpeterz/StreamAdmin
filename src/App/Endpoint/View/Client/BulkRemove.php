<?php

namespace App\Endpoint\View\Client;

use App\R7\Set\ApirequestsSet;
use App\R7\Set\AvatarSet;
use App\R7\Set\NoticeSet;
use App\R7\Set\RentalSet;
use App\R7\Set\ServerSet;
use App\R7\Set\StreamSet;
use App\Template\Form;

class BulkRemove extends RenderList
{
    protected array $hidden_clients = [];
    protected function loader(): void
    {
        $whereconfig = [
        "fields" => ["expireUnixtime"],
        "values" => [time()],
        "types" => ["i"],
        "matches" => ["<="],
         ];
        $this->rentalSet = new RentalSet();
        $this->rentalSet->loadWithConfig($whereconfig);
        $this->serverSet = new ServerSet();
        $this->serverSet->loadAll();
        $this->avatarSet = new AvatarSet();
        $this->avatarSet->loadIds($this->rentalSet->getAllByField("avatarLink"));
        $this->streamSet = new StreamSet();
        $this->streamSet->loadIds($this->rentalSet->getAllByField("streamLink"));
        $this->noticeSet = new NoticeSet();
        $this->noticeSet->loadIds($this->rentalSet->getAllByField("noticeLink"));
        $this->apiRequestsSet = new ApirequestsSet();
        $this->apiRequestsSet->loadAll();
    }
    public function process(): void
    {
        global $unixtime_day;
        $this->setSwapTag("html_title", "Clients");
        $this->output->addSwapTagString("page_title", "Bulk remove");
        $this->setSwapTag("page_actions", "");

        $table_head = ["id","Action","Avatar","Server","Port","NoticeLevel","Expired"];
        $table_body = [];

        $this->loader();

        $used_stream_ids = $this->apiRequestsSet->getUniqueArray("streamLink");

        $unixtime_oneday_ago = time() - $unixtime_day;

        foreach ($this->rentalSet->getAllIds() as $rental_id) {
            $rental = $this->rentalSet->getObjectByID($rental_id);
            $avatar = $this->avatarSet->getObjectByID($rental->getAvatarLink());
            $stream = $this->streamSet->getObjectByID($rental->getStreamLink());
            $server = $this->serverSet->getObjectByID($stream->getServerLink());
            $notice = $this->noticeSet->getObjectByID($rental->getNoticeLink());
            if ($rental->getMessage() != null) {
                if (strlen($rental->getMessage()) > 0) {
                    $this->hidden_clients[] = [
                    "why" => "Message on account",
                    "rentaluid" => $rental->getRentalUid(),
                    "avatar" => $avatar->getAvatarName(),
                    "port" => $stream->getPort(),
                    ];
                    continue;
                }
            }
            if (in_array($stream->getId(), $used_stream_ids) == true) {
                $this->hidden_clients[] = [
                "why" => "Pending api request",
                "rentaluid" => $rental->getRentalUid(),
                "avatar" => $avatar->getAvatarName(),
                "port" => $stream->getPort(),
                ];
                continue;
            }
            $entry = [];
            $entry[] = $rental->getId();
            $action = $this->makeButton($rental->getRentalUid(), "", "checked");
            if (($notice->getId() == 6) && ($rental->getExpireUnixtime() < $unixtime_oneday_ago)) {
                $action = $this->makeButton($rental->getRentalUid());
            }
            $entry[] = $action;
            $entry[] = $avatar->getAvatarName();
            $entry[] = $server->getDomain();
            $entry[] = $stream->getPort();
            $entry[] = $notice->getName();
            $entry[] = expiredAgo($rental->getExpireUnixtime());
            $table_body[] = $entry;
        }

        $this->setSwapTag("page_content", "No clients to remove right now");
        if (count($table_body) > 0) {
            $form = new Form();
            $form->target("client/bulkremove");
            $form->col(12);
              $form->directAdd($this->renderDatatable($table_head, $table_body));
            $this->setSwapTag("page_content", $form->render("Process", "outline-danger"));
        }
        $this->hiddenClientsView();
    }


    protected function makeButton(
        string $name = "",
        string $removechecked = "checked",
        string $skipchecked = ""
    ): string {
        return '
<div class="btn-group btn-group-toggle" data-toggle="buttons">
  <label class="btn btn-outline-danger active">
    <input type="radio" value="purge" name="rental' . $name . '" autocomplete="off" ' . $removechecked . '> Remove
  </label>
  <label class="btn btn-outline-secondary">
    <input type="radio" value="keep" name="rental' . $name . '" autocomplete="off" ' . $skipchecked . '> Skip
  </label>
</div>';
    }

    protected function hiddenClientsView(): void
    {
        if (count($this->hidden_clients) > 0) {
            $this->output->addSwapTagString("page_content", "<hr/><h4>Unlisted clients</h4>");
            $table_head = ["Why","Rental UID","Avatar","Port"];
            $table_body = [];
            foreach ($this->hidden_clients as $hclient) {
                $entry = [];
                $entry[] = $hclient["why"];
                $entry[] = $hclient["rentaluid"];
                $entry[] = $hclient["avatar"];
                $entry[] = $hclient["port"];
                $table_body[] = $entry;
            }
            $this->output->addSwapTagString("page_content", $this->renderTable($table_head, $table_body));
        }
    }
}
