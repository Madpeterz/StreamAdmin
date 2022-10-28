<?php

namespace App\Endpoint\View\Slconfig;

use YAPF\Bootstrap\Template\Form;

class PaymentKey extends View
{
    public function getKeyStatus(?string $checkKey, bool $giveTimeleft = true): string
    {
        if ($checkKey === null) {
            return "No key";
        }
        $keyCheck = explode(":", $checkKey);
        if (count($keyCheck) != 3) {
            return "Invaild";
        }
        $webCheck = sha1($keyCheck[0] . "" . $keyCheck[1] . "web");
        $webCheck = substr($webCheck, 0, 3);
        if ($webCheck != $keyCheck[2]) {
            return "Failed";
        }
        if (time() > $keyCheck[1]) {
            return "Expired";
        }
        if ($giveTimeleft == true) {
            return $this->timeRemainingHumanReadable($keyCheck[1], false, "Expired");
        }
        return "ok";
    }
    public function process(): void
    {
        $this->setSwapTag("page_actions", "");
        $this->setSwapTag("html_title", " Payment key");
        $this->setSwapTag("page_title", " Payment key ~ Status: "
        . $this->getKeyStatus($this->siteConfig->getSlConfig()->getPaymentKey()));

        $form = new Form();
        $form->target("Slconfig/PaymentKeyUpdate");
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
