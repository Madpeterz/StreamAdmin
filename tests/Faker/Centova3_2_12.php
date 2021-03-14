<?php

namespace App\Faker;

class Centova3_2_12
{
    /*
        replys are based on v3.2.12 using real world tests
    */
    protected $required_post_keys = [];
    protected $a_args = [];
    public function __construct()
    {
        if (defined("UNITTEST") == false) {
            die("error - attempting to load faker system while outside of testing");
        }
        $this->load();
    }

    protected function haveAllRequiredArgs(): bool
    {
        global $_POST;
        $a_array = $_POST["a"];
        foreach ($this->a_args as $a_arg) {
            if (array_key_exists($a_arg, $a_array) == false) {
                print "a_arg missing: " . $a_arg;
                return false;
            }
        }
        return true;
    }

    protected function hasRequiredPostKeys(): bool
    {
        global $_POST;
        foreach ($this->required_post_keys as $postkey) {
            if (array_key_exists($postkey, $_POST) == false) {
                print "post key missing: " . $postkey;
                return false;
            }
        }
        return true;
    }

    public function load(): void
    {
        $this->required_post_keys[] = "xm";
        $this->required_post_keys[] = "f";
        $this->required_post_keys[] = "a";
        $this->a_args[] = "password";
        if ($this->hasRequiredPostKeys() == false) {
            return;
        }
        $function_name = "";
        $bits = explode(".", $_POST["xm"]);
        foreach ($bits as $bit) {
            if ($function_name != "") {
                $function_name .= ucfirst($bit);
            } else {
                $function_name .= $bit;
            }
        }
        if (method_exists($this, $function_name) == false) {
            print "Function is not mocked";
            return;
        }
        $query = "server";
        if (substr($function_name, 0, strlen($query)) === $query) {
            $this->a_args[] = "username";
        }
        $this->$function_name();
    }

    protected function basicReply(string $functionname, array $data_addon = []): void
    {
        if ($this->haveAllRequiredArgs() == false) {
            return;
        }
        $reply = [
            "type" => "success",
            "response" => [],
        ];
        $response = [
            "message" => "This is a faked reply for: " . $functionname,
        ];
        $response = array_merge($response,$data_addon);
        $reply["response"] = $response;
        print json_encode($reply);
    }

    protected function serverManagedj(): void
    {
        $this->a_args[] = "action";
        $reply_addon = [
            "data" => [],
        ];
        $this->basicReply(__FUNCTION__,$reply_addon);
    }

    protected function serverGetaccount(): void
    {
        $reply_addon = [
            "data" => [
                "status" => true,
            ],
        ];
        $this->basicReply(__FUNCTION__,$reply_addon);
    }

    protected function systemVersion(): void
    {
        $reply_addon = [
            "data" => [
                "web" => [
                    "other" => [
                        "Load (1m)" => rand(0,5),
                        "Load (5m)" => rand(0,5),
                        "Load (15m)" => rand(0,5),
                    ],
                    "accounts" => 100,
                    "activeaccounts" => 83,
                    "memfree" => (rand(4,5) * 100000),
                    "memtotal" => ((15+rand(4,5)) * 100000),
                ],
            ],
        ];
        $this->basicReply(__FUNCTION__,$reply_addon); 
    }

    protected function systemListaccounts(): void
    {
        $this->a_args[] = "start";
        $this->a_args[] = "limit";
        if ($this->haveAllRequiredArgs() == false) {
            return;
        }
        $reply_addon = [];
    }

    protected function systemSetstatus(): void
    {
        $this->a_args[] = "username";
        $this->a_args[] = "limit";
        $this->basicReply(__FUNCTION__);
    }

    protected function serverReconfigure(): void
    {
        if (array_key_exists("title", $_POST["a"]) == true) {
            $this->a_args[] = "title";
        } else {
            $this->a_args[] = "adminPassword";
            $this->a_args[] = "sourcepassword";
        }
        $this->basicReply(__FUNCTION__);
    }

    protected function serverStart(): void
    {
        $this->a_args[] = "noapps";
        $this->basicReply(__FUNCTION__);
    }

    protected function serverStop(): void
    {
        $this->basicReply(__FUNCTION__);
    }

    protected function serverNextsong(): void
    {
        $this->basicReply(__FUNCTION__);
    }

    protected function serverGetstatus(): void
    {
        $this->a_args[] = "mountpoints";
        $this->basicReply(__FUNCTION__, [
            "status" => [
                "serverstate" => 1,
                "sourcestate" => 1,
                "sourcetype" => "liquidsoap",
            ],
        ]);
    }

    protected function serverSwitchsource(): void
    {
        $this->a_args[] = "state";
        $this->basicReply(__FUNCTION__);
    }

    protected function systemRename(): void
    {
        $this->a_args[] = "username";
        $this->a_args[] = "newusername";
        $this->basicReply(__FUNCTION__);
    }

    protected function systemProvision(): void
    {
        $this->a_args[] = "port";
        $this->a_args[] = "maxclients";
        $this->a_args[] = "adminPassword";
        $this->a_args[] = "sourcepassword";
        $this->a_args[] = "maxbitrate";
        $this->a_args[] = "username";
        $this->a_args[] = "email";
        $this->a_args[] = "usesource";
        $this->a_args[] = "autostart";
        $this->a_args[] = "template";
        $this->basicReply(__FUNCTION__);
    }
}
