<?php

namespace App\Endpoint\View\Slconfig;

use YAPF\Bootstrap\Template\Form;
use YAPF\Framework\Responses\DbObjects\SingleLoadReply;

class Paymentkey extends View
{
    public function getKeyStatus(?string $checkKey, bool $giveTimeleft = true): SingleLoadReply
    {
        $key = $this->input->varInput($checkKey)->checkStringLength(23, 23)->asString();
        if ($key == null) {
            return new SingleLoadReply("key failed checks: " . $this->input->getWhyFailed());
        } elseif ($checkKey === null) {
            return new SingleLoadReply("No Key");
        }
        $keyCore = explode("*", $checkKey);
        if (count($keyCore) != 2) {
            return new SingleLoadReply("Old");
        }
        $webHash = $keyCore[1];
        $keyCheck = explode(":", $keyCore[0]);
        if (count($keyCheck) != 2) {
            return new SingleLoadReply("Invaild");
        }
        $webCheck = sha1($keyCheck[0] . $this->siteConfig->getSiteURL() . $keyCheck[1] . "web");
        $webCheck = substr($webCheck, 0, 3);
        if ($webCheck != $webHash) {
            return new SingleLoadReply("Failed");
        }
        if (time() > $keyCheck[1]) {
            return new SingleLoadReply("Expired");
        }
        if ($giveTimeleft == true) {
            return new SingleLoadReply($this->timeRemainingHumanReadable($keyCheck[1], false, "Expired"), true);
        }
        return new SingleLoadReply("ok", true);
    }
    public function process(): void
    {
        $this->setSwapTag("page_actions", "");
        $this->setSwapTag("html_title", " Payment key");
        $this->setSwapTag("page_title", " Payment key ~ Status: "
        . $this->getKeyStatus($this->siteConfig->getSlConfig()->getPaymentKey())->message);

        $form = new Form();
        $form->target("Slconfig/Paymentkeyupdate");
        $form->required(true);
        $form->col(3);
        $form->textInput(
            "assignedkey",
            "key",
            23,
            $this->siteConfig->getSlConfig()->getPaymentKey(),
            "Keys are not free :P"
        );
        $form->col(2);
        $form->directAdd(" ");
        $form->col(7);
        $form->directAdd("Link to online payment gateway will appear here when it is ready<br/> 
        Details about payment options here<br/> 
        And maybe something about tax.<br/>
        <br/>
        Company logo here<br/>
        <sub>Companyname here</sub><br/>
        <br/>
        Thank you for supporting StreamAdmin");
        $this->setSwapTag("page_content", $form->render("Register key", "success"));

        if ($this->siteConfig->getSlConfig()->getPaymentKey() != null) {
            $bits = explode(":", $this->siteConfig->getSlConfig()->getPaymentKey());
            if (count($bits) == 3) {
                $this->output->addSwapTagString("page_content", '<hr/>
            <p>SL code: <textarea class="form-control col-2" cols="3" rows="1" readonly>
            ' . $bits[0] . ':' . $bits[1] . '</textarea></p>');
            }
        }
    }
}
