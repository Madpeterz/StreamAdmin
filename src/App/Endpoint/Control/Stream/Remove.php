<?php

namespace App\Endpoints\Control\Stream;

use App\Models\Stream;
use App\Models\TransactionsSet;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Remove extends ViewAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $accept = $input->postFilter("accept");
        $this->output->setSwapTagString("redirect", "stream");
        if ($accept != "Accept") {
            $this->output->setSwapTagString("message", "Did not Accept");
            $this->output->setSwapTagString("redirect", "stream/manage/" . $this->page . "");
            return;
        }
        $stream = new Stream();
        if ($stream->loadByField("stream_uid", $this->page) == false) {
            $this->output->setSwapTagString("message", "Unable to find stream");
            return;
        }
        $transaction_set = new TransactionsSet();
        $load_status = $transaction_set->loadOnField("streamlink", $stream->getId());
        if ($load_status["status"] == false) {
            $this->output->setSwapTagString("message", sprintf("Unable to load transactions linked to stream because: %1\$s", $load_status["message"]));
            return;
        }
        if ($transaction_set->getCount() > 0) {
            $bulkupdate_status = $transaction_set->updateFieldInCollection("streamlink", null);
            if ($bulkupdate_status["status"] == false) {
                $this->output->setSwapTagString("message", sprintf("Unable to unlink transactions from stream because: %1\$s", $bulkupdate_status["message"]));
                return;
            }
        }

        $remove_status = $stream->removeEntry();
        if ($remove_status["status"] == false) {
            $this->output->setSwapTagString("message", sprintf("Unable to remove stream: %1\$s", $remove_status["message"]));
            return;
        }
        $this->output->setSwapTagString("status", "true");
        $this->output->setSwapTagString("message", "Stream removed");
    }
}
