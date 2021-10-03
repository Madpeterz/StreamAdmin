<?php

namespace App\Endpoint\View\Client;

use App\R7\Set\ApirequestsSet;
use App\R7\Set\AvatarSet;
use App\R7\Set\DetailSet;
use App\R7\Set\RentalSet;
use App\R7\Set\ServerSet;
use App\R7\Set\StreamSet;
use App\Template\Form;

class BulkRemove extends RenderList
{
    protected DetailSet $detailsRequestsSet;
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
        $this->avatarSet->loadByValues($this->rentalSet->getAllByField("avatarLink"));
        $this->streamSet = new StreamSet();
        $this->streamSet->loadByValues($this->rentalSet->getAllByField("streamLink"));
        $this->apiRequestsSet = new ApirequestsSet();
        $this->apiRequestsSet->loadAll();
        $this->detailsRequestsSet = new DetailSet();
        $this->detailsRequestsSet->loadByValues($this->rentalSet->getAllIds(), "rentalLink");
    }
    public function process(): void
    {
        global $unixtime_day;
        $this->setSwapTag("html_title", "Clients");
        $this->output->addSwapTagString("page_title", "Bulk remove");
        $this->setSwapTag("page_actions", "");

        $table_head = ["id","Action","Avatar","Server","Port","Expired","Message"];
        $table_body = [];

        $this->loader();

        $used_stream_ids = $this->apiRequestsSet->getUniqueArray("streamLink");

        foreach ($this->rentalSet as $rental) {
            $avatar = $this->avatarSet->getObjectByID($rental->getAvatarLink());
            $stream = $this->streamSet->getObjectByID($rental->getStreamLink());
            $server = $this->serverSet->getObjectByID($stream->getServerLink());
            $detail = $this->detailsRequestsSet->getObjectByField("rentalLink", $rental->getId());
            if ($detail != null) {
                continue;
            }
            if (in_array($stream->getId(), $used_stream_ids) == true) {
                continue;
            }
            if ($rental->getNoticeLink() != 6) {
                continue;
            }

            $entry = [];
            $entry[] = $rental->getId();
            $entry[] = $this->makeButton($rental->getRentalUid());
            $entry[] = $avatar->getAvatarName();
            $entry[] = $server->getDomain();
            $entry[] = $stream->getPort();
            $entry[] = expiredAgo($rental->getExpireUnixtime());
            $entry[] = '<textarea readonly class="form-control" id="message" 
            name="message" placeholder="" cols="35" rows="2">
            ' . $rental->getMessage() . '</textarea>';
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
    }


    protected function makeButton(
        string $name = ""
    ): string {
        return '
<div class="btn-group btn-group-toggle" data-toggle="buttons">
  <label class="btn btn-outline-danger active">
    <input type="radio" value="purge" name="rental' . $name . '" autocomplete="off"> Remove
  </label>
  <label class="btn btn-outline-secondary">
    <input type="radio" value="keep" name="rental' . $name . '" autocomplete="off" checked> Skip
  </label>
</div>';
    }
}
