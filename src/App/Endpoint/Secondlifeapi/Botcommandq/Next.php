<?php

namespace App\Endpoint\Secondlifeapi\Botcommandq;

use App\Helpers\BotHelper;
use App\Models\Avatar;
use App\Models\Botcommandq;
use App\Models\Botconfig;
use App\Models\Sets\BotcommandqSet;
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

    protected function setupBot(): bool
    {
        if ($this->botconfig == null) {
            $this->botconfig = new Botconfig();
            $this->botconfig->loadID(1);
        }
        if ($this->botconfig->isLoaded() == false) {
            $this->failed("Unable to load bot config");
            return false;
        }
        if ($this->bot == null) {
            $this->bot = new Avatar();
            $this->bot->loadID($this->botconfig->getAvatarLink());
        }
        if ($this->bot->isLoaded() == false) {
            $this->failed("Unable to load bot avatar config");
            return false;
        }
        return true;
    }

    protected function setupChecks(): bool
    {
        if (($this->connectedViaCron == true) && ($this->botconfig->getHttpMode() == false)) {
            $this->failed("Do not run botcommandQ in cron without HTTP enabled!");
            return false;
        }
        if (($this->connectedViaCron == false) && ($this->botconfig->getHttpMode() == true)) {
            $this->failed("Do not run botcommandQ via SL with HTTP enabled!");
            return false;
        }
        return true;
    }

    public function process(): void
    {
        if ($this->hasAccessOwner() == false) {
            return;
        }
        if ($this->setupBot() == false) {
            return;
        }
        if ($this->setupChecks() == false) {
            return;
        }
        $botcommandQset = new BotcommandqSet();
        $loadStatus = $botcommandQset->loadNewest(limit:1, orderDirection:"ASC"); // load oldest
        if ($loadStatus->status == false) {
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

    /**
     * It takes a Botcommandq object, and returns an array of results.  The results array has two elements:
     *  "status" and "message".  The "status" element is a boolean, and the "message" element is a string.
     * The "status" element is true if the command was successful, and false if it was not.  The "message"
     * element is a string that contains the results of the command
     * @param Botcommandq command The command to execute
     * @return array<mixed> An array with two elements:
     * * status: true or false
     * * message: the message to be sent to the user
     */
    protected function httpCommands(Botcommandq $command): array
    {
        $args = [];
        if ($command->getArgs() !== null) {
            $args = json_decode($command->getArgs());
        }

        $results = ["status" => false,"message" => "Unknown command: " . $command->getCommand()];
        if (($command->getCommand() == "IM") && (count($args) == 2)) {
            $endpoint = "chat/IM/" . $args[0] . "/" . $this->botconfig->getHttpToken();
            $results = $this->restPost($endpoint, ["message" => $args[1]]);
        } elseif (($command->getCommand() == "GroupInvite") && (count($args) == 3)) {
            $args[] = $this->botconfig->getHttpToken();
            $bits = implode("/", $args);
            $endpoint = "group/GroupInvite/" . $bits;
            $results = $this->restGet($endpoint);
        } elseif (($command->getCommand() == "FetchNextNotecard") && (count($args) == 2)) {
            $postArgs = [
                "endpoint" => $args[0],
                "endpointcode" => $args[1],
            ];
            $endpoint = "streamadmin/FetchNextNotecard/" . $this->botconfig->getHttpToken();
            $results = $this->restPost($endpoint, $postArgs);
        }
        return $results;
    }

    protected function processAsHTTP(Botcommandq $command): void
    {
        $results = $this->httpCommands($command);

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
            $this->failed($results["message"]);
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
        if ($reply->status == false) {
            $this->failed("Unable to remove command from the Q because:" . $reply->message);
            return false;
        }
        return true;
    }

    protected array $options = [];
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
            }
            return [
                "status" => false,
                "message" => "http error [" . $endpoint . "] :"
                . $res->getStatusCode() . " : " . $res->getBody()->getContents(),
            ];
        } catch (Exception $e) {
            return ["status" => false,"message" => "[" . $endpoint . "] Request failed in a fireball"];
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
