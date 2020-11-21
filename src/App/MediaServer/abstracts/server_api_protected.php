<?php

require_once("webpanel/vendor/autoload.php");
abstract class server_rest_api extends error_logging
{
    protected $stream = null;
    protected $server = null;
    protected $package = null;
    protected $client = null;

    protected $options = [];


    function __construct(?stream $stream, ?server $server, ?package $package)
    {
        $this->stream = $stream;
        $this->server = $server;
        $this->package = $package;
    }

    public function update_package(package $package): void
    {
        $this->package = $package;
        $this->client = null;
    }
    public function update_server(server $server): void
    {
        $this->server = $server;
        $this->client = null;
    }
    public function update_stream(stream $stream): void
    {
        $this->stream = $stream;
        $this->client = null;
    }
    protected function get_client_auth(): void
    {
    }
    protected function get_post_formated(array $postdata = []): array
    {
        return ['form_params' => $postdata];
    }
    protected function make_guzzle(): void
    {
        if ($this->client == null) {
            $this->options = [];
            $this->options['base_uri'] = $this->server->getApi_url();
            $this->options['allow_redirects'] = true;
            $this->options['timeout'] = 15;
            $this->options['http_errors'] = false;
            $this->get_client_auth();
            $this->client = new \GuzzleHttp\Client($this->options);
        }
    }
    protected function rest_process(string $method, string $endpoint, array $postdata = [])
    {
        $this->make_guzzle();
        try {
            $body = [];
            if (count($postdata) > 0) {
                $body = $this->get_post_formated($postdata);
            }
            $res = $this->client->request($method, $endpoint, $body);
            if ($res->getStatusCode() == 200) {
                return ["status" => true,"message" => $res->getBody()->getContents()];
            } else {
                return ["status" => false,"message" => "http error:" . $res->getStatusCode()];
            }
        } catch (Exception $e) {
            return ["status" => false,"message" => "Request failed in a fireball"];
        }
    }
    protected function rest_get(string $endpoint): array
    {
        return $this->rest_process('GET', $endpoint);
    }
    protected function rest_post(string $endpoint, array $args = []): array
    {
        return $this->rest_process('POST', $endpoint, $args);
    }
    protected function rest_delete(string $endpoint, array $args = []): array
    {
        return $this->rest_process("DELETE", $endpoint, $args);
    }
    protected function rest_put(string $endpoint, array $args = []): array
    {
        return $this->rest_process("PUT", $endpoint, $args);
    }
}
abstract class server_api_protected extends server_rest_api
{
    protected $last_api_message = "";
    protected $needs_retry = false;
    protected function stream_state(): array
    {
        $this->last_api_message = "Skipped stream_state not supported on this api";
        return ["status" => false,"state" => false,"source" => false];
    }
    protected function terminate_account(string $old_username): bool
    {
        $this->last_api_message = "Skipped terminate_account not supported on this api";
        return true;
    }
    protected function create_account(): bool
    {
        $this->last_api_message = "Skipped create_account not supported on this api";
        return true;
    }
    protected function dj_list(): array
    {
        $this->last_api_message = "Skipped dj_list not supported on this api";
        return ["status" => true,"list" => []];
    }
    protected function remove_dj(string $djaccount): bool
    {
        $this->last_api_message = "Skipped remove_dj not supported on this api";
        return true;
    }
    protected function account_state(): array
    {
        $this->last_api_message = "Skipped account_state not supported on this api";
        return ["status" => false,"state" => false];
    }
    protected function account_name_list(bool $include_passwords = false, stream_set $stream_set = null): array
    {
        return ["status" => false,"usernames" => [],"message" => "account_name_list supported on this api"];
    }
    protected function sync_username(string $old_username): bool
    {
        $this->last_api_message = "Skipped not supported on this api";
        return true;
    }
    protected function server_status(): array
    {
        return ["status" => false,"loads" => ["1" => 0,"5" => 0,"15" => 0],"ram" => ["free" => 0,"max" => 0],"streams" => ["total" => 0,"active" => 0],"message" => "This api does not support server status"];
    }
    protected function toggle_autodj(): bool
    {
        $this->last_api_message = "Skipped not supported on this api";
        return true;
    }
    protected function autodj_next(): bool
    {
        $this->last_api_message = "Skipped not supported on this api";
        return true;
    }
    protected function stop_server(): bool
    {
        $this->last_api_message = "Skipped not supported on this api";
        return true;
    }
    protected function start_server(): bool
    {
        $this->last_api_message = "Skipped not supported on this api";
        return true;
    }
    protected function susspend_server(): bool
    {
        $this->last_api_message = "Skipped not supported on this api";
        return true;
    }
    protected function un_susspend_server(): bool
    {
        $this->last_api_message = "Skipped not supported on this api";
        return true;
    }
    protected function change_password(): bool
    {
        $this->last_api_message = "Skipped not supported on this api";
        return true;
    }
    protected function change_title_now(string $newtitle): bool
    {
        $this->last_api_message = "Skipped not supported on this api";
        return true;
    }
}
