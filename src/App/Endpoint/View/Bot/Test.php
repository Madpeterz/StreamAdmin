<?php

namespace App\Endpoint\View\Bot;

use App\Framework\Menu;
use App\Helpers\BasicReply;
use App\Models\Botconfig;
use Exception;
use GuzzleHttp\Client;

class Test extends Menu
{
    protected ?Client $client = null;

    public function process(): void
    {
        if ($this->siteConfig->getSession()->getOwnerLevel() == false) {
            $this->output->redirect("config?bubblemessage=Owner level access needed&bubbletype=warning");
            return;
        }
        $reply = $this->httpCommands("Hello");
        if ($reply->status == false) {
            $this->output->redirect("Bot?bubblemessage=" . $reply->message . "&bubbletype=warning");
            return;
        }
        $jsonReply = json_decode($reply->message, true);
        if (array_key_exists("Key", $jsonReply) == false) {
            $this->output->redirect(
                "Bot?bubblemessage=Reply is not formated as expected Missing Key in reply&bubbletype=warning"
            );
            return;
        }
        if (array_key_exists("Value", $jsonReply) == false) {
            $this->output->redirect(
                "Bot?bubblemessage=Reply is not formated as expected Missing Value in reply&bubbletype=warning"
            );
            return;
        }
        if ($jsonReply["Key"] != true) {
            $this->output->redirect(
                "Bot?bubblemessage=Reply is not formated as expected Key is not true&bubbletype=warning"
            );
            return;
        }
        $jsonReply = json_decode($jsonReply["Value"], true);
        if (array_key_exists("status", $jsonReply) == false) {
            $this->output->redirect(
                "Bot?bubblemessage=Reply is not formated as expected missing status code in Value&bubbletype=warning"
            );
            return;
        }
        if (array_key_exists("reply", $jsonReply) == false) {
            $this->output->redirect(
                "Bot?bubblemessage=Reply is not formated as expected missing reply code in Value&bubbletype=warning"
            );
            return;
        }
        if ($jsonReply["status"] == false) {
            $this->output->redirect("Bot?bubblemessage=" . $jsonReply["reply"] . "&bubbletype=warning");
            return;
        }
        if ($jsonReply["reply"] != "world") {
            $this->output->redirect(
                "Bot?bubblemessage=incorrect reply expected world but got " .
                    $jsonReply["reply"] . "&bubbletype=warning"
            );
            return;
        }
        $this->output->redirect("Bot?bubblemessage=Bot HTTP test passed&bubbletype=success");
    }
    protected function httpCommands(string $command, array $args = []): BasicReply
    {
        $botconfig = new Botconfig();
        $botconfig->loadID(1);
        if ($botconfig->getHttpMode() == false) {
            return new BasicReply(message: "HTTP not enabled");
        }
        $this->makeHTTPClient($botconfig->getHttpURL());
        $commandArgs = [
            "commandName" => $command,
            "unixtime" => time(),
            "signing" => "",
            "args" => implode("~#~", $args),
        ];
        $raw = $commandArgs["commandName"] . $commandArgs["args"] .
            $commandArgs["unixtime"] . $botconfig->getSecret();
        $commandArgs["signing"] = sha1($raw);
        return $this->restPost("api/Run", $commandArgs);
    }
    /**
     * getPostFormated
     * @return mixed[] [form_params => array]
     */
    protected function getPostFormated(array $postdata = []): array
    {
        return ['form_params' => $postdata];
    }
    /**
     * restPost
     * @return mixed[] [status => bool, message => string]
     */
    protected function restPost(string $endpoint, array $args = []): BasicReply
    {
        $method = "POST";
        try {
            $body = [];
            if (count($args) > 0) {
                $body = $this->getPostFormated($args);
            }
            $res = $this->client->request($method, $endpoint, $body);
            if ($res->getStatusCode() == 200) {
                return new BasicReply(true, $res->getBody()->getContents());
            }
            return new BasicReply(message: "http error [" . $endpoint . "] :"
                . $res->getStatusCode() . " : " . $res->getBody()->getContents());
        } catch (Exception $e) {
            return new BasicReply(message: "[" . $endpoint . "] Request failed in a fireball");
        }
    }

    protected array $options = [];
    protected function makeHTTPClient(string $url): ?Client
    {
        $this->options = [];
        $this->options['base_uri'] = $url;
        $this->options['allow_redirects'] = true;
        $this->options['timeout'] = 7;
        $this->options['http_errors'] = false;
        $this->client = new Client($this->options);
        return $this->client;
    }
}
