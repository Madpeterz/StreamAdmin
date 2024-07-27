<?php

namespace App\Endpoint\Secondlifeapi\Botcommandq;

use App\Helpers\BotHelper;
use App\Models\Avatar;
use App\Models\Botcommandq;
use App\Models\Botconfig;
use App\Models\Message;
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



    public function process(): void
    {
        $this->failed("starting stage 1 checks");
        if ($this->hasAccessOwner() == false) {
            return;
        }
        $this->failed("mid stage 1 checks");
        if ($this->setupBot() == false) {
            return;
        }
        $this->failed("past stage 1 checks");
        $botcommandQset = new BotcommandqSet();
        $loadStatus = $botcommandQset->loadNewest(limit: 1, orderDirection: "ASC"); // load oldest
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
        $commandArgs = [
            "commandName" => $command->getCommand(),
            "unixtime" => time(),
            "signing" => "",
            "args" => implode("~#~", $args),
        ];
        $raw = $commandArgs["commandName"] . $commandArgs["args"] .
            $commandArgs["unixtime"] . $this->botconfig->getSecret();
        $commandArgs["signing"] = sha1($raw);
        return $this->restPost("api/Run", $commandArgs);
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
        $message = new Message();
        $message->setAvatarLink($bothelper->getBotAvatarLink());
        $message->setMessage($formatedCmd);
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
                return ["status" => true, "message" => $res->getBody()->getContents()];
            }
            return [
                "status" => false,
                "message" => "http error [" . $endpoint . "] :"
                    . $res->getStatusCode() . " : " . $res->getBody()->getContents(),
            ];
        } catch (Exception $e) {
            return ["status" => false, "message" => "[" . $endpoint . "] Request failed in a fireball"];
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
