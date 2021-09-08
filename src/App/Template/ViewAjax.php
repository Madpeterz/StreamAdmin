<?php

namespace App\Template;

abstract class ViewAjax extends View
{
    protected string $method = "";
    protected string $action = "";
    public function __construct(bool $AutoLoadTemplate = false)
    {
        parent::__construct($AutoLoadTemplate);
        $this->output->tempateAjax();
    }
    public function getoutput(): void
    {
        $this->renderPage();
    }
    public function renderPage(): void
    {
        $this->forceSave();
        $this->output->renderAjax();
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
}
