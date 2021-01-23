<?php

namespace App\Endpoint\View\Client;

use App\Models\ApirequestsSet;
use App\Models\AvatarSet;
use App\Models\NoticeSet;
use App\Models\RentalSet;
use App\Models\ServerSet;
use App\Models\StreamSet;
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
        $whereconfig = [];
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
            if (strlen($rental->getMessage()) != 0) {
                $this->hidden_clients[] = [
                  "why" => "Message on account",
                  "rentaluid" => $rental->getRentalUid(),
                  "avatar" => $avatar->getAvatarName(),
                  "port" => $stream->getPort(),
                ];
            } elseif (in_array($stream->getId(), $used_stream_ids) == true) {
                $this->hidden_clients[] = [
                "why" => "Pend ing api request",
                "rentaluid" => $rental->getRentalUid(),
                "avatar" => $avatar->getAvatarName(),
                "port" => $stream->getPort(),
                ];
            } else {
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
        }

        $this->setSwapTag("page_content", "No clients to remove right now");
        if (count($table_body) > 0) {
            $form = new Form();
            $form->target("client/bulkremove");
            $form->col(12);
              $form->directAdd($this->renderDatatable($table_head, $table_body));
            $this->setSwapTag("page_content", $form->render("Process", "outline-danger"));
        }
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

    protected function hiddeenClients(): void
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
