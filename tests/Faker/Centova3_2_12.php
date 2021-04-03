<?php

namespace tests\Faker;

class Centova3_2_12
{
    /*
        replys are based on v3.2.12 using real world tests
        replys are shorted to just what is needed to give a
        vaild reply not all values are returned.

        no data is loaded from DB for this fake some values
        will be generated randomly for fun
    */
    protected $required_post_keys = [];
    protected $a_args = [];
    public function __construct()
    {
        //error_log("inbound centova call ".$_SERVER['REQUEST_URI'].": ".json_encode($_POST));
        if (defined("UNITTEST") == false) {
            die("error - attempting to load faker system while outside of testing");
        }
        $this->load();
    }

    protected function haveAllRequiredArgs(string $functionname): bool
    {
        global $_POST;
        $a_array = $_POST["a"];
        foreach ($this->a_args as $a_arg) {
            if (array_key_exists($a_arg, $a_array) == false) {
                print $functionname." / a_arg missing: " . $a_arg;
                return false;
            }
        }
        return true;
    }

    protected function hasRequiredPostKeys(string $functionname): bool
    {
        global $_POST;
        foreach ($this->required_post_keys as $postkey) {
            if (array_key_exists($postkey, $_POST) == false) {
                print $functionname." / post key missing: " . $postkey;
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
        if ($this->hasRequiredPostKeys("first load") == false) {
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
            print "Function: ".$function_name." is not mocked";
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
        if ($this->haveAllRequiredArgs($functionname) == false) {
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
        if($_POST["a"]["action"] == "terminate")
        {
            $this->basicReply(__FUNCTION__);
            return;
        }
        else
        {
            // flip a weighted coin see if you get the broken api reply :P
            if(rand(0,6) == 1) {
                $reply_addon = [
                    "data" => [],
                ];
                $this->basicReply(__FUNCTION__,$reply_addon);
                return;
            }
            $reply = [
                "type" => "failure",
                "response" => [
                    "message" => "Invalid argument supplied for foreach()",
                ],
            ];
            print json_encode($reply);
            return;
        }
    }

    protected function serverGetaccount(): void
    {
        $reply_addon = [
            "data" => [
                "account" => [
                    "status" => "enabled",
                    "adminPassword" => substr(sha1(microtime()."asdasd"),0,10),
                    "sourcepassword" => substr(sha1(microtime()."dfhdfhdfg"),0,10),
                ],
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
                        "Load (1m)" => [0,rand(0,100)/100,0],
                        "Load (5m)" => [0,rand(0,100)/100,0],
                        "Load (15m)" => [0,rand(0,100)/100,0],
                    ],
                    "accounts" => 100,
                    "activeaccounts" => rand(50,80),
                    "memfree" => (rand(4000,5000) * 120000),
                    "memtotal" => ((1500+rand(4000,5000)) * 120000),
                ],
            ],
        ];
        $this->basicReply(__FUNCTION__,$reply_addon); 
    }

    protected function systemListaccounts(): void
    {
        $this->a_args[] = "start";
        $this->a_args[] = "limit";
        if ($this->haveAllRequiredArgs(__FUNCTION__) == false) {
            return;
        }
        $randomnames = [];
        while(count($randomnames) < 10) {
            $randomnames[] = ["username" => "fake".substr(sha1(microtime()."asdasd"),0,10)];
        }
        $reply_addon = [
            "data" => $randomnames,
        ];
        $this->basicReply(__FUNCTION__,$reply_addon);
    }

    protected function systemSetstatus(): void
    {
        $this->a_args[] = "username";
        $this->a_args[] = "status";
        $this->basicReply(__FUNCTION__);
    }

    protected function systemTerminate(): void
    {
        $this->a_args[] = "username";
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
            "data" => [
                "status" => [
                    "serverstate" => 1,
                    "sourcestate" => 1,
                    "sourcetype" => "liquidsoap",
                ],
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
