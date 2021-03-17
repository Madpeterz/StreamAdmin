<?php

namespace tests\Faker;

class Azurecast
{
    protected array $bits = [];
    protected string $authToken = "faked";
    public function __construct($auth_token=null)
    {
        if($auth_token != null) {
            $this->authToken = $auth_token;
        }
        if (defined("UNITTEST") == false) {
            $this->Failure("error - attempting to load faker system while outside of testing");
        }
        $this->load();
    }

    protected function Failure(string $why): void
    {
        http_response_code(403);
        print json_encode(
            [
                "status" => false,
                "message" => $why
            ]
        );
        die();
    }

    protected function haveFields(array $fields, array $source)
    {
        foreach($fields as $field)
        {
            if(array_key_exists($field,$source) == false)
            {
                $this->Failure("Required field: ".$field." is missing!");
            }
        }
    }

    protected function haveAnyField(array $fields, array $source)
    {
        foreach($fields as $field)
        {
            if(array_key_exists($field,$source) == true)
            {
                return;
            }
        }
        $this->Failure("Requires 1 of the following fields: ".implode(",",$fields)." to be in the dataset:".json_encode($source)."");
    }

    protected function minbits(int $min)
    {
        if(count($this->bits) < $min)
        {
            $this->Failure("Required url args missing expected ".$min." but got: ".count($this->bits));
        }
    }

    protected function load(): void
    {
        // header: Authorization
        if($this->authToken != null)
        {
            foreach (getallheaders() as $name => $value) {
                if(strtolower($name) == "authorization") {
                    if($value != "Bearer ".$this->authToken)
                    {
                        $this->Failure("Access denied");
                    }
                }
            }
        }

        $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
        $this->bits = array_values(array_diff(explode("/", $uri_parts[0]), [""]));

        $endpoints = [
            "status" => "systemStatus",
            "station" => [
                "*" => [
                    "streamer" => "stationStreamer",
                    "streamers" => "stationStreamers",
                    "status" => "stationStatus",
                    "backend" => "stationBackend",
                    "frontend" => "stationFrontend",
                ],
            ],
            "admin" => [
                "user" => "adminUser",
                "users" => "adminUsers",
            ],
        ];
        $retry = true;
        while(($retry == true) && (count($this->bits) > 0))
        {
            if (strpos($this->bits[0], "fake") !== false) {
                array_shift($this->bits);
                continue;
            }
            if (strpos($this->bits[0], ".php") !== false) {
                array_shift($this->bits);
                continue;
            }
            $retry = false;
        }
        if(count($this->bits) >= 1) {
            if(array_key_exists($this->bits[0],$endpoints) == false)
            {
                $this->Failure("no endpoint: ".json_encode($this->bits));
                return;
            }
            $found = false;
            $load_function = "";
            if(is_array($endpoints[$this->bits[0]]) == false)
            {
                $found = true;
                $load_function = $endpoints[$this->bits[0]];
            }
            if(count($this->bits) >= 2) {
                if($found == false)
                {
                    if(array_key_exists($this->bits[1],$endpoints[$this->bits[0]]) == true)
                    {
                        if(is_array($endpoints[$this->bits[0]][$this->bits[1]]) == false)
                        {
                            $found = true;
                            $load_function = $endpoints[$this->bits[0]][$this->bits[1]];
                        }
                    }
                }
            }
            if(count($this->bits) >= 3) {
                if($found == false)
                {
                    // widecard on step 2
                    if(array_key_exists("*",$endpoints[$this->bits[0]]) == true)
                    {
                        if(array_key_exists($this->bits[2],$endpoints[$this->bits[0]]["*"]) == true)
                        {
                            $found = true;
                            $load_function = $endpoints[$this->bits[0]]["*"][$this->bits[2]];
                        }
                    }
                }
            }
            if($found == false) 
            {
                $this->Failure("Unknown endpoint ".implode(",",$this->bits));
                return;
            }
            if(method_exists($this,$load_function) == false) {
                $this->Failure("unsupported endpoint ".$load_function);
                return;
            }
            $this->$load_function();
        }
        
    }

    protected function systemStatus(): void
    {
        $this->minbits(1);
        if($_SERVER['REQUEST_METHOD'] == "GET")
        {
            print json_encode(["online"=>true,"timestamp"=>time()]);
            return;
        }
        $this->Failure(__FUNCTION__." Unsupported method: ".$_SERVER['REQUEST_METHOD']);
        return;
    }

    protected function stationStreamer(): void
    {
        $this->minbits(4);
        if($_SERVER['REQUEST_METHOD'] == "GET")
        {
            print json_encode(
                [
                    "id" => 1,
                    "streamer_username" => "dj_test",
                    "streamer_password" => "",
                    "display_name" => "Test DJ",
                    "comments" => "This is a test DJ account.",
                    "is_active" => true,
                    "enforce_schedule" => false,
                    "reactivate_at" => 1615357697,
                    "schedule_items" => [
                        null,
                    ]
                ]
            );
            return;
        }
        else if($_SERVER['REQUEST_METHOD'] == "DELETE")
        {
            print json_encode(
                [
                    "success" => true,
                    "message" => "Changes saved successfully.",
                    "formatted_message" => "<b>Changes saved successfully.</b>"
                ]
            );
            return;
        }
        $this->Failure(__FUNCTION__." Unsupported method: ".$_SERVER['REQUEST_METHOD']);
    }

    protected function stationStreamers(): void
    {
        $this->minbits(3);
        if($_SERVER['REQUEST_METHOD'] == "GET")
        {
            print json_encode(
                [
                    [
                        "id" => 1,
                        "streamer_username" => "dj_test",
                        "streamer_password" => "",
                        "display_name" => "Test DJ",
                        "comments" => "This is a test DJ account.",
                        "is_active" => true,
                        "enforce_schedule" => false,
                        "reactivate_at" => 1615357697,
                        "schedule_items" => [
                            null,
                        ]
                    ],
                    [
                        "id" => 2,
                        "streamer_username" => "dj_test2",
                        "streamer_password" => "",
                        "display_name" => "Test DJ2",
                        "comments" => "This is a test DJ account.",
                        "is_active" => false,
                        "enforce_schedule" => true,
                        "reactivate_at" => 1615357697,
                        "schedule_items" => [
                            null,
                        ]
                    ],
                ]
            );
            return;
        }
        $this->Failure(__FUNCTION__." Unsupported method: ".$_SERVER['REQUEST_METHOD']);
    }

    protected function stationStatus(): void
    {
        $this->minbits(3);
        if($_SERVER['REQUEST_METHOD'] == "GET")
        {
            print json_encode(
                [
                    "frontend_running" => true,
                    "backend_running" => true,
                ]
            );
            return;
        }
        $this->Failure(__FUNCTION__." Unsupported method: ".$_SERVER['REQUEST_METHOD']);
    }

    protected function stationBackend(): void
    {
        $this->minbits(4);
        if($_SERVER['REQUEST_METHOD'] == "POST")
        {
            $allowed_actions = ["start","stop","restart","skip","disconnect"];
            if(in_array($this->bits[3],$allowed_actions) == false)
            {
                $this->Failure("Unknown action: ".$_POST["action"]);
            }
            print json_encode(["success"=>true]);
            return;
        }
        $this->Failure(__FUNCTION__." Unsupported method: ".$_SERVER['REQUEST_METHOD']);
    }

    protected function stationFrontend(): void
    {
        $this->minbits(4);
        if($_SERVER['REQUEST_METHOD'] == "POST")
        {
            $allowed_actions = ["start","stop","restart"];
            if(in_array($this->bits[3],$allowed_actions) == false)
            {
                $this->Failure("Unknown action: ".$_POST["action"]);
            }
            print json_encode(["success"=>true]);
            return;
        }
        $this->Failure(__FUNCTION__." Unsupported method: ".$_SERVER['REQUEST_METHOD']);
    }

    protected function adminUser(): void
    {
        $this->minbits(3);
        if($_SERVER['REQUEST_METHOD'] == "GET")
        {
            print json_encode(
                [
                    "id" => $this->bits[2],
                    "email"=> "demo@azuracast.com",
                    "new_password"=> "",
                    "name"=> "Demo Account",
                    "locale"=> "en_US",
                    "theme"=> "dark",
                    "two_factor_secret"=> "A1B2C3D4",
                    "created_at"=> 1615357697,
                    "updated_at"=> 1615357697,
                    "roles"=> [
                      null
                    ],
                ]
            );
            return;
        }
        else if($_SERVER['REQUEST_METHOD'] == "PUT")
        {
            $put = json_decode(file_get_contents('php://input'),true);
            if (is_array($put) == false) {
                $this->Failure(__FUNCTION__." Unable to process put values [Server config have it them disabled]");
            }
            $this->haveAnyField(["email","new_password","name","roles"],$put);
            print json_encode(
                [
                    "success" => true,
                    "message" => "Changes saved successfully.",
                    "formatted_message" => "<b>Changes saved successfully.</b>"
                ]
            );
            return;
        }
        else if($_SERVER['REQUEST_METHOD'] == "DELETE")
        {
            print json_encode(
                [
                    "success" => true,
                    "message" => "Changes saved successfully.",
                    "formatted_message" => "<b>Changes saved successfully.</b>"
                ]
            );
            return;
        }
        $this->Failure(__FUNCTION__." Unsupported method: ".$_SERVER['REQUEST_METHOD']);
    }

    protected function adminUsers(): void
    {
        $this->minbits(2);
        if($_SERVER['REQUEST_METHOD'] == "GET")
        {
            print json_encode(
                [
                    [
                        "id" => 1,
                        "email" => "demo@azuracast.com",
                        "new_password" => "",
                        "name" => "Demo Account",
                        "locale" => "en_US",
                        "theme" => "dark",
                        "two_factor_secret" => "A1B2C3D4",
                        "created_at" => 1615357697,
                        "updated_at" => 1615357697,
                        "roles" =>  [
                          null
                        ]
                    ],
                    [
                        "id" => 2,
                        "email" => "demo2@azuracast.com",
                        "new_password" => "",
                        "name" => "Demo Account2",
                        "locale" => "en_US",
                        "theme" => "dark",
                        "two_factor_secret" => "A1B2C3D4",
                        "created_at" => 1715357697,
                        "updated_at" => 1715357697,
                        "roles" =>  [
                          null
                        ]
                    ],
                ]
            );
            return;
        }
        else if($_SERVER['REQUEST_METHOD'] == "POST")
        {
            $this->haveFields(["email","new_password","name","roles"],$_POST);  
            print json_encode(
                [
                    "id" => 4,
                    "email"=> "demo@azuracast.com",
                    "new_password"=> "",
                    "name"=> "Demo Account",
                    "locale"=> "en_US",
                    "theme"=> "dark",
                    "two_factor_secret"=> "A1B2C3D4",
                    "created_at"=> time(),
                    "updated_at"=> time(),
                    "roles"=> [
                      null
                    ],
                ]
            );
            return;
        }
        $this->Failure(__FUNCTION__." Unsupported method: ".$_SERVER['REQUEST_METHOD']);
    }
}
