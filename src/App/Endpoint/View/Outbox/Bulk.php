<?php

namespace App\Endpoint\View\Outbox;

use App\Models\Sets\AvatarSet;
use App\Models\Sets\BanlistSet;
use App\Models\Notice;
use App\Models\Package;
use App\Models\Sets\RentalSet;
use App\Models\Server;
use App\Models\Sets\StreamSet;
use YAPF\Bootstrap\Template\Form;

class Bulk extends View
{
    public function process(): void
    {
        $rental_set = new RentalSet();

        $input_filter = new InputFilter();
        $source_id = -1;
        $souce_named = "";
        $ok = false;
        $message = "";


        if ($this->siteConfig->getPage() == "notice") {
            $message = $input_filter->getString("messageStatus", 800, 10);
            if ($message == null) {
                $this->output->redirectWithMessage("outbox", "message failed:" . $input_filter->getWhyFailed(), "warning");
                return;
            }
            $source_id = $input_filter->getFilter("noticeLink", "integer");
            if ($source_id != null) {
                $notice = new Notice();
                $notice->loadID($source_id);
                $souce_named = $notice->getName();
                $rental_set->loadOnField("noticeLink", $source_id);
                $ok = true;
            }
        } elseif ($this->siteConfig->getPage() == "server") {
            $message = $input_filter->getString("messageServer", 800, 10);
            if ($message == null) {
                $this->output->redirectWithMessage("outbox", "message failed:" . $input_filter->getWhyFailed(), "warning");
                return;
            }
            $source_id = $input_filter->getFilter("serverLink", "integer");
            if ($source_id != null) {
                $server = new Server();
                $server->loadID($source_id);
                $souce_named = $server->getDomain();
                $stream_set = new StreamSet();
                $stream_set->loadOnField("serverLink", $source_id);
                $rental_set->loadByValues($stream_set->getAllIds(), "streamLink");
                $ok = true;
            }
        } elseif ($this->siteConfig->getPage() == "package") {
            $message = $input_filter->getString("messagePackage", 800, 10);
            if ($message == null) {
                $this->output->redirectWithMessage("outbox", "message failed:" . $input_filter->getWhyFailed(), "warning");
                return;
            }
            $source_id = $input_filter->getFilter("packageLink", "integer");
            if ($source_id != null) {
                $package = new Package();
                $package->loadID($source_id);
                $souce_named = $package->getName();
                $rental_set->loadOnField("packageLink", $source_id);
                $ok = true;
            }
        } elseif ($this->siteConfig->getPage() == "clients") {
            $message = $input_filter->getString("messageClients", 800, 10);
            if ($message == null) {
                $this->output->redirectWithMessage("outbox", "message failed:" . $input_filter->getWhyFailed(), "warning");
                return;
            }
            $rental_set->loadAll();
            $souce_named = "clients";
            $source_id = 1;
            $ok = true;
        }
        if (strlen($message) < 10) {
            $this->output->redirectWithMessage("outbox", "Message is to short", "warning");
            return;
        }

        if ($ok == false) {
            $this->output->redirectWithMessage("outbox", "Filter option not supported", "warning");
            return;
        }

        $this->output->addSwapTagString("page_title", " Bulk sending to " . $this->siteConfig->getPage() . ": " . $souce_named);
        $stream_set = new StreamSet();
        $stream_set->loadByValues($rental_set->getAllByField("streamLink"));
        $avatar_set = new AvatarSet();
        $avatar_set->loadByValues($rental_set->getUniqueArray("avatarLink"));
        $banlist_set = new BanlistSet();
        $banlist_set->loadByValues($rental_set->getUniqueArray("avatarLink"), "avatarLink");

        $max_avatar_count = $avatar_set->getCount() - $banlist_set->getCount();
        if ($max_avatar_count == 0) {
            $this->output->redirect("outbox?message=No selectable avatars for the " . $this->siteConfig->getPage());
            return;
        }
        $form = new Form();
        $form->target("outbox/send");
        $form->hiddenInput("message", $message);
        $form->hiddenInput("max_avatars", $max_avatar_count);
        $form->hiddenInput("source", $this->siteConfig->getPage());
        $form->hiddenInput("source_id", $source_id);

        $table_head = ["<a href=\"#\" class=\"bulksenduncheck\">X</a>","Name"];
        $table_body = [];

        $banned_ids = $banlist_set->getAllByField("avatarLink");
        foreach ($avatar_set as $avatar) {
            if (in_array($avatar->getId(), $banned_ids) == false) {
                $entry = [];
                $entry[] = '<div class="checkbox"><input checked type="checkbox" id="avatarmail' . $avatar->getId()
                . '" name="avatarids[]" value="' . $avatar->getId() . '"></div>';
                $entry[] = '<div class="checkbox"><label for="avatarmail'
                . $avatar->getId() . '">' . $avatar->getAvatarName() . '</label></div>';
                $table_body[] = $entry;
            }
        }
        $form->col(12);
            $form->directAdd($this->renderTable($table_head, $table_body));
        $this->setSwapTag("page_content", $form->render("Send to selected", "success"));
        $this->output->addSwapTagString("page_content", "<br/><hr/>Note: If an avatar has multiple streams that "
        . "match the selected filter source the first rental will be used.");
    }
}
