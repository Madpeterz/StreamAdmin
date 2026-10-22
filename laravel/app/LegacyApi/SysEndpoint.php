<?php

namespace App\LegacyApi;

use App\Support\SystemApiKey;

/**
 * sys.php endpoints: calls from the bot (port of 2.0.x SystemApiAjax). Signed with the HTTP
 * inbound secret, or a short lived key derived from it for this method/action.
 */
abstract class SysEndpoint extends Endpoint
{
    protected int $unixtime = 0;

    protected string $token = '';

    protected string $method = '';

    protected string $action = '';

    public function boot(): void
    {
        $this->requiredValues();
        if ($this->loadOk && ! $this->inTimeWindow($this->unixtime)) {
            $this->loadOk = false;
            $this->failed('timewindow is out of scope');
        }
        if ($this->loadOk) {
            $this->tokenCheck();
        }
        if ($this->loadOk) {
            $this->failed('ready');
        } else {
            $this->set('status', false);
        }
    }

    private function requiredValues(): void
    {
        foreach (['unixtime', 'token', 'method', 'action'] as $field) {
            $value = $field === 'unixtime' ? $this->input->int($field) : $this->input->stringMin($field, 1);
            if ($value === null) {
                $this->loadOk = false;
                $this->failed('Value: '.$field.' is missing');

                return;
            }
            $this->{$field} = $value;
        }
    }

    private function tokenCheck(): void
    {
        $secret = (string) $this->slConfig()->http_inbound_secret;
        if (strlen($secret) < 5) {
            $this->loadOk = false;
            $this->failed('httpcode length must be 5 or longer');

            return;
        }
        if (strlen($secret) > 30) {
            $this->loadOk = false;
            $this->failed('httpcode length must be 30 or less');

            return;
        }
        foreach ([$secret, ...SystemApiKey::accepted($secret, $this->method, $this->action)] as $code) {
            if (hash_equals(sha1($this->unixtime.$this->method.$this->action.$code), $this->token)) {
                return;
            }
        }
        $this->loadOk = false;
        $this->failed('Invaild token');
    }
}
