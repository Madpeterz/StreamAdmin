<?php

namespace App\Endpoint\View\Home;

use YAPF\Bootstrap\Template\Form;
use YAPF\Bootstrap\Template\Grid;

class DefaultView extends HomeDisplayData
{
    public function process(): void
    {
        $this->main_grid = new Grid();
        if ($this->siteConfig->getSession()->getOwnerLevel() == 1) {
            $this->unsafeWorkspace();
        }
        $this->loadDatasets();
        $this->displayDatasets();
        $this->output->addSwapTagString("page_content", $this->main_grid->getOutput());
        if ($this->siteConfig->getSlConfig()->getPaymentKey() == null) {
            $this->setSwapTag(
                "page_actions",
                "<a href='[[SITE_URL]]Slconfig/PaymentKey'>
                <button type='button' class='btn btn-danger'>No key!</button></a>"
            );
        }
        $this->setSwapTag(
            "page_actions",
            "<a href='[[SITE_URL]]Slconfig/PaymentKey'>
            <button type='button' class='btn btn-danger'>Invaild key</button></a>"
        );
        if ($this->siteConfig->getSlConfig()->getPaymentKey() != null) {
            $webPart = explode("*", $this->siteConfig->getSlConfig()->getPaymentKey());
            if (count($webPart) != 2) {
                return;
            }
            $keyCheck = explode(":", $webPart[0]);
            if (count($keyCheck) != 2) {
                return;
            }
            if (time() > $keyCheck[1]) {
                $this->setSwapTag(
                    "page_actions",
                    "<a href='[[SITE_URL]]Slconfig/PaymentKey'>
                    <button type='button' class='btn btn-danger'>Expired key</button></a>"
                );
                return;
            }
            $webCheck = sha1($keyCheck[0] . "" . $keyCheck[1] . "web");
            $webCheck = substr($webCheck, 0, 3);
            if ($webCheck != $keyCheck[2]) {
                $this->setSwapTag(
                    "page_actions",
                    "<a href='[[SITE_URL]]Slconfig/PaymentKey'>
                    <button type='button' class='btn btn-danger'>Invaild key</button></a>"
                );
                return;
            }
            $dif = $keyCheck[1] - time();
            $mins = floor(($dif / 60));
            $hours = floor(($mins / 60));
            $days = floor($hours / 24);
            $weeks = floor($days / 7);
            $timeleftNice = $this->timeRemainingHumanReadable($keyCheck[1], false, "Expired");
            $color = "info";
            if ($weeks < 8) {
                $color = "warning";
            }
            if ($weeks < 4) {
                $color = "danger";
            }
            $this->setSwapTag(
                "page_actions",
                "<a href='[[SITE_URL]]Slconfig/PaymentKey'>
                <button type='button' class='btn btn-" . $color . "'>" . $timeleftNice . "</button></a>"
            );
            if (($weeks > (4 * 5))) {
                $this->setSwapTag(
                    "page_actions",
                    "<a href='[[SITE_URL]]Slconfig/PaymentKey'>
                    <button type='button' class='btn btn-outline-dark'>
                    <i class=\"fas fa-check text-success\"></i></button></a>"
                );
            }
            return;
        }
        $this->setSwapTag(
            "page_actions",
            "<a href='[[SITE_URL]]Slconfig/PaymentKey'>
            <button type='button' class='btn btn-danger'>No Key!</button></a>"
        );
        return;
    }

    protected function unsafeWorkspace(): void
    {
        $why_unsafe = "";
        if (is_dir('../../tests') == true) {
            $why_unsafe .= " tests folder found ";
        }
        if ($why_unsafe != "") {
            $this->main_grid->addContent('<div class="jumbotron">
            <h1 class="display-4">please make your install secure</h1>
            <p class="lead">' . $why_unsafe . '</p>
          </div>', 12);
        }
    }
}
