<?php

namespace App\Template;

use App\Framework\SessionControl;
use App\Models\Slconfig;
use YAPF\MySQLi\MysqliEnabled;

abstract class ViewAjax extends View
{
    public function __construct(bool $AutoLoadTemplate = false)
    {
        parent::__construct($AutoLoadTemplate);
        $this->output->tempateAjax();
    }

    public function renderPage(): void
    {
        $this->output->renderAjax();
    }
}
