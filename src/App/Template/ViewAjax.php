<?php

namespace App\Template;

abstract class ViewAjax extends View
{
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
        $this->output->renderAjax();
    }
}
