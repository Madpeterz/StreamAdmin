<?php

namespace App\Endpoint\SecondLifeApi\BotCommandQ;

use App\Helpers\BotHelper;
use App\R7\Model\Avatar;
use App\R7\Model\Botcommandq;
use App\R7\Model\Botconfig;
use App\R7\Set\BotcommandqSet;
use App\Template\SecondlifeAjax;
use Exception;
use GuzzleHttp\Client;

class Next extends SecondlifeAjax
{
    protected bool $connectedViaCron = false;
    protected ?Botconfig $botconfig = null;
    protected ?Avatar $bot = null;

    // HTTP
    protected ?Client $client = null;
    public function setCronConnected(): void
    {
        $this->connectedViaCron = true;
    }
    public function attachBotConfig(Botconfig $config): void
    {
        $this->botconfig = $config;
    }
    public function attachBotAvatar(Avatar $av): void
    {
        $this->bot = $av;
    }
    public function attachHTTPClient(Client $httpClient): void
    {
        $this->client = $httpClient;
    }
    public function makeHTTPClient(): ?Client
    {
        $this->makeGuzzle($this->botconfig->getHttpURL());
        return $this->client;
    }

    public function process(): void
    {
        if ($this->owner_override == false) {
            $this->setSwapTag("message", "SystemAPI access only - please contact support");
            return;
        }
        if ($this->botconfig == null) {
            $this->botconfig = new Botconfig();
            $this->botconfig->loadID(1);
        }
        if ($this->botconfig->isLoaded() == false) {
            $this->failed("Unable to load bot config");
            return;
        }
        if ($this->bot == null) {
            $this->bot = new Avatar();
            $this->bot->loadID($this->botconfig->getAvatarLink());
        }
        if ($this->bot->isLoaded() == false) {
            $this->failed("Unable to load bot avatar config");
            return;
        }
        if (($this->connectedViaCron == true) && ($this->botconfig->getHttpMode() == false)) {
            $this->failed("Do not run botcommandQ in cron without HTTP enabled!");
            return;
        }
        if (($this->connectedViaCron == false) && ($this->botconfig->getHttpMode() == true)) {
            $this->failed("Do not run botcommandQ via SL with HTTP enabled!");
            return;
        }
        $botcommandQset = new BotcommandqSet();
        $loadStatus = $botcommandQset->loadNewest(1, [], [], "id", "ASC"); // load oldest
        if ($loadStatus["status"] == false) {
            $this->failed("Unable to load command Q");
            return;
        }
        if ($botcommandQset->getCount() == 0) {
            $this->ok("nowork"); // nothing todo
            return;
        }
        if ($this->botconfig->getHttpMode() == true) {
            $this->processAsHTTP($botcommandQset->getFirst());
            return;
        }
        $this->processAsObjectIM($botcommandQset->getFirst());
    }

    protected function processAsHTTP(Botcommandq $command): void
    {
        $args = [];
        if ($command->getArgs() != null) {
            $args = json_decode($command->getArgs());
        }
        $results = ["status" => false,"message" => "Unknown command: " . $command];
        if (($command == "IM") && (count($args) == 2)) {
            $endpoint = "chat/IM/" . $args[0] . "/" . $this->botconfig->getHttpToken();
            $results = $this->restPost($endpoint, ["message" => $args[1]]);
        } elseif (($command == "GroupInvite") && (count($args) == 3)) {
            $args[] = $this->botconfig->getHttpToken();
            $bits = explode("/", $args);
            $endpoint = "group/GroupInvite/" . $bits;
            $results = $this->restGet($endpoint);
        } elseif (($command == "FetchNextNotecard") && (count($args) == 2)) {
            $postArgs = [
                "endpoint" => $args[0],
                "endpointcode" => $args[1],
            ];
            $endpoint = "streamadmin/FetchNextNotecard/" . $this->botconfig->getHttpToken();
            $results = $this->restPost($endpoint, $postArgs);
        }
        if ($results["status"] == false) {
            $this->failed($results["message"]);
            return;
        }
        $jsonReply = json_decode($results["message"], true);
        if (array_key_exists("status", $jsonReply) == false) {
            $this->failed("Reply is not formated as expected " . $results["message"]);
        }
        if (array_key_exists("reply", $jsonReply) == false) {
            $this->failed("Reply is not formated as expected " . $results["message"]);
        }
        if ($jsonReply["status"] == false) {
            $this->failed($jsonReply["reply"]);
            return;
        }
        if ($this->removeCommand($command) == false) {
            return;
        }
        $this->ok($jsonReply["reply"]);
    }

    protected function processAsObjectIM(Botcommandq $command): void
    {
        $bothelper = new BotHelper();
        $bothelper->attachBotSetup($this->bot, $this->botconfig);
        $formatedCmd = $bothelper->getBotCommand($command->getCommand(), []);
        if ($command->getArgs() != null) {
            $formatedCmd = $bothelper->getBotCommand($command->getCommand(), json_decode($command->getArgs()));
        }
        if ($this->removeCommand($command) == false) {
            return;
        }
        $this->setSwapTag("cmd", $formatedCmd);
        $this->setSwapTag("avatar", $this->bot->getAvatarUUID());
        $this->ok("send");
    }

    protected function removeCommand(Botcommandq $command): bool
    {
        $reply = $command->removeEntry();
        if ($reply["status"] == false) {
            $this->failed("Unable to remove command from the Q because:" . $reply["message"]);
            return false;
        }
        return true;
    }


    /**
     * getPostFormated
     * @return mixed[] [form_params => array]
     */
    protected function getPostFormated(array $postdata = []): array
    {
        return ['form_params' => $postdata];
    }
    protected function makeGuzzle(string $baseURL): void
    {
        if ($this->client == null) {
            $this->options = [];
            $this->options['base_uri'] = $baseURL;
            $this->options['allow_redirects'] = true;
            $this->options['timeout'] = 7;
            $this->options['http_errors'] = false;
            $this->client = new Client($this->options);
        }
    }
    /**
     * restProcess
     * @return mixed[] [status => bool, message => string]
     */
    protected function restProcess(string $method, string $endpoint, array $postdata = []): array
    {
        try {
            $body = [];
            if (count($postdata) > 0) {
                $body = $this->getPostFormated($postdata);
            }
            $res = $this->client->request($method, $endpoint, $body);
            if ($res->getStatusCode() == 200) {
                return ["status" => true,"message" => $res->getBody()->getContents()];
            } else {
                return [
                    "status" => false,
                    "message" => "http error:" . $res->getStatusCode() . " : " . $res->getBody()->getContents(),
                ];
            }
        } catch (Exception $e) {
            return ["status" => false,"message" => "Request failed in a fireball"];
        }
    }
    /**
     * restGet
     * @return mixed[] [status => bool, message => string]
     */
    protected function restGet(string $endpoint): array
    {
        return $this->restProcess('GET', $endpoint);
    }
    /**
     * restPost
     * @return mixed[] [status => bool, message => string]
     */
    protected function restPost(string $endpoint, array $args = []): array
    {
        return $this->restProcess('POST', $endpoint, $args);
    }
}
