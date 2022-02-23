<?php

namespace YAPF\Bootstrap\Template;

use YAPF\Bootstrap\ConfigBox\BootstrapConfigBox;
use YAPF\Bootstrap\Template\Output\Template;
use YAPF\Core\ErrorControl\ErrorLogging;

abstract class BasicView extends ErrorLogging
{
    protected array $allowDisallow = [0 => "Disabled",1 => "Allow"];
    protected array $yesNo = [0 => "No",1 => "Yes"];
    protected array $disableEnable = [false => "Disabled",true => "Enabled"];
    protected Template $output;
    protected BootstrapConfigBox $config;

    public function __construct(
        bool $AutoLoadTemplate = true
    ) {
        global $system;
        $this->config = $system;

        $this->output = new Template($AutoLoadTemplate);
        if ($AutoLoadTemplate == true) {
            $this->output->tempateSidemenu();
        }
    }
    public function forceSave(): void
    {
        if ($this->output->getSwapTagBool("status") == false) {
            $this->config->getSQL()->sqlRollBack();
            return;
        }
        $this->config->getSQL()->sqlSave(true);
    }
    public function process(): void
    {
        $this->setSwapTag("status", false);
        $this->failed("Not processsed yet");
    }
    protected function ok(string $message): void
    {
        $this->setMessage($message, true);
    }
    protected function failed(string $message): void
    {
        $this->setMessage($message, false);
    }
    protected function setMessage(string $message, bool $status): void
    {
        $this->setSwapTag("status", $status);
        $this->setSwapTag("message", $message);
    }
    protected bool $load_ok = true;
    public function getLoadOk(): bool
    {
        return $this->load_ok;
    }
    public function getOutputObject(): Template
    {
        return $this->output;
    }
    public function renderPage(): void
    {
        $this->loadMenu();
        $this->output->renderPage();
    }
    public function getoutput(): void
    {
        $this->renderPage();
    }
    protected function setSwapTag($tag, $value): void
    {
        $this->output->setSwapTag($tag, $value);
    }
    protected function loadMenu(): void
    {
        $this->setSwapTag("html_menu", "");
    }
}
