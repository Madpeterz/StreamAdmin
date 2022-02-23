<?php

namespace YAPF\Bootstrap\Template;

class View extends TableView
{
    protected function makeDynamicReport(string $target, string $loadingText = null): string
    {
        $waitingText = '
        <button class="btn btn-primary" type="button" disabled>
<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
Loading...
</button>';
        if ($loadingText != null) {
            $waitingText = $loadingText;
        }
        $bits = [microtime(),$target];
        $rand = rand(0, 5);
        $updateId = substr(sha1(implode("#", $bits)), $rand, 12);
        $updateId = "aj" . $updateId . "rp";
        return '<div id="' . $updateId . '" class="autoload" data-update="' . $updateId . '" 
        data-target="[[SITE_URL]]' . $target . '">' . $waitingText . '</div>';
    }
}
