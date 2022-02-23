<?php

namespace YAPF\Bootstrap\Template;

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
}
