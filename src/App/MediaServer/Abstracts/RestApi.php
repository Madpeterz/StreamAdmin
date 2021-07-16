<?php

namespace App\MediaServer\Abstracts;

use App\R7\Model\Package;
use App\R7\Model\Server;
use App\R7\Model\Stream;
use Exception;
use YAPF\Core\ErrorControl\ErrorLogging;

abstract class RestApi extends ErrorLogging
{
    protected $stream = null;
    protected $server = null;
    protected $package = null;
    protected $client = null;

    protected $options = [];


    public function __construct(?Stream $stream, ?Server $server, ?Package $package)
    {
        $this->stream = $stream;
        $this->server = $server;
        $this->package = $package;
    }

    public function updatePackage(package $package): void
    {
        $this->package = $package;
        $this->client = null;
    }
    public function updateServer(server $server): void
    {
        $this->server = $server;
        $this->client = null;
    }
    public function updateStream(stream $stream): void
    {
        $this->stream = $stream;
        $this->client = null;
    }
    protected function getClientAuth(): void
    {
    }
    /**
     * getPostFormated
     * @return mixed[] [form_params => array]
     */
    protected function getPostFormated(array $postdata = []): array
    {
        return ['form_params' => $postdata];
    }
    protected function makeGuzzle(): void
    {
        if ($this->client == null) {
            $this->options = [];
            $this->options['base_uri'] = $this->server->getApiURL();
            $this->options['allow_redirects'] = true;
            $this->options['timeout'] = 15;
            $this->options['http_errors'] = false;
            $this->getClientAuth();
            $this->client = new \GuzzleHttp\Client($this->options);
        }
    }
    /**
     * restProcess
     * @return mixed[] [status => bool, message => string]
     */
    protected function restProcess(string $method, string $endpoint, array $postdata = []): array
    {
        $this->makeGuzzle();
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
    /**
     * restDelete
     * @return mixed[] [status => bool, message => string]
     */
    protected function restDelete(string $endpoint, array $args = []): array
    {
        return $this->restProcess("DELETE", $endpoint, $args);
    }
    /**
     * restPut
     * @return mixed[] [status => bool, message => string]
     */
    protected function restPut(string $endpoint, array $args = []): array
    {
        return $this->restProcess("PUT", $endpoint, $args);
    }
}
