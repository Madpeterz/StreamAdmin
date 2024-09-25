<?php

namespace App\Endpoint\Secondlifeapi\Botcommandq;

use App\Helpers\BotHelper;
use App\Models\Avatar;
use App\Models\Botcommandq;
use App\Models\Botconfig;
use App\Models\Message;
use App\Models\Sets\BotcommandqSet;
use App\Models\Sets\MessageSet;
use App\Template\SecondlifeAjax;
use Exception;
use GuzzleHttp\Client;

class Next extends SecondlifeAjax
{
    protected bool $connectedViaCron = false;

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
        if ($this->client === null) {
            $this->makeGuzzle($this->botconfig->getHttpURL());
        }
        return $this->client;
    }
    public function process(): void
    {
        if ($this->hasAccessOwner() == false) {
            return;
        }
        if ($this->setupBot() == false) {
            return;
        }
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
        $selectedCommand = $botcommandQset->getFirst();
        if ($this->output->getSwapTagBool("status") == true) {
            if ($selectedCommand->removeEntry()->status == false) {
                $this->failed("Unable to mark command as processed");
                return;
            }
        }
        if ($this->botconfig->getHttpMode() == true) {
            if ($this->makeHTTPClient() === null) {
                $this->failed("Unable to setup http endpoint");
                return;
            }
            $this->processAsHTTP($selectedCommand);
            return;
        }
        $this->processAsObjectIM($selectedCommand);
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
        $raw = $results["message"];
        $jsonReply = json_decode($results["message"], true);
        if (array_key_exists("Key", $jsonReply) == false) {
            $this->failed("Reply is not formated as expected Missing Key in repl: " . $raw);
            return;
        }
        if (array_key_exists("Value", $jsonReply) == false) {
            $this->failed("Reply is not formated as expected Missing Value in reply: " . $raw);
            return;
        }
        if ($jsonReply["Key"] != true) {
            $this->failed("Reply is not formated as expected Key is not true: " . $raw);
            return;
        }
        $jsonReply = json_decode($jsonReply["Value"], true);
        if (array_key_exists("status", $jsonReply) == false) {
            $this->failed("Reply is not formated as expected missing status code in Value: " . $raw);
            return;
        }
        if (array_key_exists("reply", $jsonReply) == false) {
            $this->failed("Reply is not formated as expected missing reply code in Value: " . $raw);
            return;
        }
        if ($jsonReply["status"] == false) {
            $this->failed($results["reply"]);
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
        $messageSet = new MessageSet();
        $count = $messageSet->countInDB([
            "fields" => ["avatarLink"],
            "values" => [$bothelper->getBotAvatarLink()],
        ]);
        if ($count->items > 0) {
            $this->failed("To many pending mail commands to the bot");
            return;
        }
        if ($this->sendMessageToAvatar($this->bot, $formatedCmd)->status == false) {
            $this->failed("Failed to send message to bot");
            return;
        }
        $this->ok("passed command to mail server");
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
     * restPost
     * @return mixed[] [status => bool, message => string]
     */
    protected function restPost(string $endpoint, array $args = []): array
    {
        $method = "POST";
        try {
            $body = [];
            if (count($args) > 0) {
                $body = $this->getPostFormated($args);
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
}
