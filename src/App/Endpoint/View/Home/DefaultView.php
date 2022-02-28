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
        $keyCheck = explode(":", $this->siteConfig->getSlConfig()->getPaymentKey());
        if (count($keyCheck) != 3) {
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
        $timeleftNice = timeleftHoursAndDays($keyCheck[1], false, "Expired");
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

    protected function unsafeWorkspace(): void
    {
        $need_cleanup = false;
        $why_unsafe = "";
        if (is_dir('fake') == true) {
            $need_cleanup = true;
            $why_unsafe = "faker public folder found";
        }
        if (is_dir(DEEPFOLDERPATH . '/tests') == true) {
            $need_cleanup = true;
            if ($why_unsafe != "") {
                $why_unsafe .= " , ";
            }
            $why_unsafe .= " tests folder found ";
        }
        if ($need_cleanup == true) {
            $form = new Form();
            $form->mode("post");
            $form->target("home/cleanup");
            $formcode = $form->render("Cleanup", "danger");
            $this->main_grid->addContent('<div class="jumbotron">
            <h1 class="display-4">Secure install</h1>
            <p class="lead">' . $why_unsafe . '</p>
            <hr class="my-4">
            <p>Please run the cleanup tool now!</p>
            <p class="lead">
                ' . $formcode . '
            </p>
          </div>', 12);
        }
    }
}
