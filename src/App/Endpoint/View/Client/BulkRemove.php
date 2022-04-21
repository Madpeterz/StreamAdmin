<?php

namespace App\Endpoint\View\Client;

use App\Models\Sets\ApirequestsSet;
use App\Models\Sets\AvatarSet;
use App\Models\Sets\DetailSet;
use App\Models\Sets\RentalSet;
use App\Models\Sets\ServerSet;
use App\Models\Sets\StreamSet;
use YAPF\Bootstrap\Template\Form;

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
        $this->avatarSet = $this->rentalSet->relatedAvatar();
        $this->streamSet = $this->rentalSet->relatedStream();
        $this->serverSet = $this->streamSet->relatedServer();
        $this->detailsRequestsSet = $this->rentalSet->relatedDetail();
    }
    public function process(): void
    {
        $this->setSwapTag("html_title", "Clients");
        $this->output->addSwapTagString("page_title", "Bulk remove");
        $this->setSwapTag("page_actions", "");

        $table_head = ["id","Action","Avatar","Server","Port","Expired","Message"];
        $table_body = [];

        $this->loader();

        foreach ($this->rentalSet as $rental) {
            $avatar = $this->avatarSet->getObjectByID($rental->getAvatarLink());
            $stream = $this->streamSet->getObjectByID($rental->getStreamLink());
            $server = $this->serverSet->getObjectByID($stream->getServerLink());
            $detail = $this->detailsRequestsSet->getObjectByField("rentalLink", $rental->getId());
            if ($detail != null) {
                continue;
            }
            if ($rental->getNoticeLink() != 6) {
                continue;
            }

            $entry = [];
            $entry[] = $rental->getId();
            $entry[] = $this->makeButton($rental->getRentalUid());
            $entry[] = '<a href="[[SITE_URL]]search?search=' . $avatar->getAvatarName() . '">'
            . $avatar->getAvatarName() . '</a>';
            $entry[] = $server->getDomain();
            $entry[] = $stream->getPort();
            $entry[] = $this->expiredAgo($rental->getExpireUnixtime());
            $entry[] = '<textarea readonly class="form-control" id="message' . $rental->getId() . '" 
            name="message" placeholder="" cols="35" rows="2">' . $rental->getMessage() . '</textarea>';
            $table_body[] = $entry;
        }

        $this->setSwapTag("page_content", "No clients to remove right now");
        if (count($table_body) > 0) {
            $form = new Form();
            $form->target("client/bulkremove");
            $form->col(12);
              $form->directAdd($this->renderDatatable($table_head, $table_body));
            $this->setSwapTag("page_content", $form->render("Process", "outline-danger"));
            $this->output->addSwapTagString(
                "page_content",
                "<br/><p>Clients with pending actions [see outbox] will not be listed!<br/>
                but might appear in the skipped counter!.</p>"
            );
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
