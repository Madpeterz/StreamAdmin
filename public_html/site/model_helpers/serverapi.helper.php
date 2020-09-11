<?php
class serverapi_helper
{
    protected $server = null;
    protected $package = null;
    protected $rental = null;
    protected $stream = null;
    protected $avatar = null;
    protected $message = "";
    protected $server_api = null;
    protected $api_config = null;
    public function get_message()
    {
        return $this->message;
    }
    function __construct(stream $stream = null)
    {
        if($stream != null)
        {
            $this->stream = $stream;
            if($this->load_server() == true)
            {
                if($this->load_api() == true)
                {
                    if($this->load_package() == true)
                    {
                        if($this->load_rental() == true)
                        {
                            $this->load_avatar();
                        }
                    }
                }
            }
        }
    }
    public function force_set_server(server $server) : bool
    {
        $this->server = $server;
        return $this->load_api();
    }
    public function force_set_rental(rental $rental) : bool
    {
        $this->rental = $rental;
        return $this->load_avatar();
    }
    public function force_set_package(package $package) : bool
    {
        $this->package = $package;
    }
    protected function load_api() : bool
    {
        $api = new apis();
        $processed = false;
        if($api->load($this->server->get_apilink()) == true)
        {
            if($api->get_id() > 1)
            {
                $this->api_config = $api;
                $server_api_name = "server_".$api->get_name()."";
                if(class_exists($server_api_name) == true)
                {
                    $this->server_api = new $server_api_name();
                    $this->message = "server API loaded";
                    return true;
                }
                else
                {
                    $this->message = "unable to load server API";
                }
            }
            else
            {
                $this->message = "Server does not support API commands";
            }
        }
        else
        {
            $this->message = "Unable to load api config";
        }
        return false;
    }
    protected function load_rental() : bool
    {
        $rental = new rental();
        if($rental->load_by_field("streamlink",$this->stream->get_id()) == true)
        {
            $this->rental = $rental;
            $this->message = "Rental loaded";
            return true;
        }
        $this->message = "Unable to load rental";
        return false;
    }
    protected function load_package() : bool
    {
        $package = new package();
        if($package->load($this->stream->get_packagelink()) == true)
        {
            $this->package = $package;
            $this->message = "Package loaded";
            return true;
        }
        $this->message = "Unable to load package";
        return false;
    }
    protected function load_server() : bool
    {
        $server = new server();
        if($server->load($this->stream->get_serverlink()) == true)
        {
            $this->message = "Server loaded";
            $this->server = $server;
            return true;
        }
        $this->message = "Unable to load server";
        return false;
    }
    protected function load_avatar() : bool
    {
        $avatar = new avatar();
        if($avatar->load($this->rental->get_avatarlink()) == true)
        {
            $this->message = "Avatar loaded";
            $this->avatar = $avatar;
            return true;
        }
        $this->message = "Unable to load avatar";
        return false;
    }
    protected function flag_check(string $flagname) : bool
    {
        $functionname = "get_".$flagname;
        if(($this->api_config->$functionname() == 1) && ($this->server->$functionname() == 1))
        {
            $this->message = "API flag ".$flagname." allowed";
            return true;
        }
        if($this->message == "") $this->message = "Server or API does not allow ";
        else $this->message .= " OR ";
        $this->message .= $flagname;
        return false;
    }
    protected function check_flags(array $flags) : bool
    {
        $flag_accepted = false;
        foreach($flags as $flag)
        {
            $flag_accepted = $this->flag_check($flag);
            if($flag_accepted == true)
            {
                break;
            }
        }
        return $flag_accepted;
    }
    function rand_string(int $length) : string
    {
        if($length < 8) $length = 8;
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        return substr(str_shuffle($chars),0,$length);
    }
    protected function update_account_state(bool $state) : bool
    {
        if($this->rental != null)
        {
            // flag to set rental to $state
        }
        $update_status = $this->server_api->set_account_state($this->stream,$this->server,$state);
        $this->message = $this->server_api->get_last_api_message();
        if($update_status == false)
        {
            // rollback here rental here as it failed
        }
        return $update_status;
    }
    public function api_enable_account() : bool
    {
        if($this->server_api != null)
        {
            if($this->check_flags(array("event_enable_start")) == true)
            {
                return $this->update_account_state(true);
            }
        }
        return false;
    }
    public function api_disable_account() : bool
    {
        if($this->server_api != null)
        {
            if($this->check_flags(array("event_disable_revoke")) == true)
            {
                return $this->update_account_state(false);
            }
        }
        return false;
    }
    public function api_serverstatus() : array
    {
        $status = false;
        $this->message = "started api_serverstatus";
        if($this->server_api != null)
        {
            if($this->check_flags(array("api_serverstatus")) == true)
            {
                return $this->server_api->get_server_status($this->server);
            }
            return array("status"=>false,"loads"=>array("1"=>0,"5"=>0,"15"=>0),"ram"=>array("free"=>0,"max"=>0),"streams"=>array("total"=>0,"active"=>0),"message"=>"API not enabled on server/api");
        }
        return array("status"=>false,"loads"=>array("1"=>0,"5"=>0,"15"=>0),"ram"=>array("free"=>0,"max"=>0),"streams"=>array("total"=>0,"active"=>0),"message"=>"No api");
    }
    public function api_reset_passwords()
    {
        global $sql;
        $status = false;
        $this->message = "started api_reset_passwords";
        if($this->server_api != null)
        {
            if($this->check_flags(array("opt_password_reset","event_reset_password_revoke")) == true)
            {
                $this->stream->set_field("adminpassword",$this->rand_string(7+rand(1,6)));
                $this->stream->set_field("djpassword",$this->rand_string(5+rand(1,3)));
                $update_status = $this->stream->save_changes();
                if($update_status["status"] == true)
                {
                    $status = $this->server_api->opt_password_reset($this->stream,$this->server);
                    $this->message = $this->server_api->get_last_api_message();
                    if($status == false)
                    {
                        $sql->flagError();
                    }
                }
                else
                {
                    $sql->flagError();
                    $this->message = "Unable to update password in db";
                }
            }
        }
        return $status;
    }
    public function api_start()
    {
        $status = false;
        if($this->server_api != null)
        {
            if($this->check_flags(array("opt_toggle_status","event_enable_start")) == true)
            {
                $status = $this->server_api->opt_toggle_status($this->stream,$this->server,true);
                $this->message = $this->server_api->get_last_api_message();
            }
        }
        return $status;
    }
    public function api_stop()
    {
        $status = false;
        if($this->server_api != null)
        {
            if($this->check_flags(array("opt_toggle_status","event_enable_start")) == true)
            {
                $status = $this->server_api->opt_toggle_status($this->stream,$this->server,false);
                $this->message = $this->server_api->get_last_api_message();
            }
        }
        return $status;
    }
    public function api_autodj_toggle()
    {
        $status = false;
        if($this->avatar != null)
        {
            if($this->package->get_autodj() == true)
            {
                if($this->check_flags(array("opt_toggle_autodj")) == true)
                {
                    $status = $this->server_api->opt_toggle_autodj($this->stream,$this->server);
                    $this->message = $this->server_api->get_last_api_message();
                }
            }
            else
            {
                $this->message = "This package does not support autoDJ";
            }
        }
        return $status;
    }
    public function api_autodj_next()
    {
        $status = false;
        if($this->avatar != null)
        {
            if($this->package->get_autodj() == true)
            {
                if($this->check_flags(array("opt_autodj_next")) == true)
                {
                    $status = $this->server_api->opt_autodj_next($this->stream,$this->server);
                    $this->message = $this->server_api->get_last_api_message();
                }
            }
            else
            {
                $this->message = "This package does not support autoDJ";
            }
        }
        return $status;
    }
    public function api_customize_username()
    {
        global $sql;
        $status = false;
        if($this->avatar != null)
        {
            if($this->check_flags(array("event_start_sync_username")) == true)
            {
                $stream_state_check = $this->server_api->get_stream_state($this->stream,$this->server);
                $this->message = $this->server_api->get_last_api_message();
                if($stream_state_check["status"] == true)
                {
                    if($stream_state_check["state"] == false)
                    {
                        $server_accounts = $this->server_api->get_account_name_list($this->server);
                        $this->message = $this->server_api->get_last_api_message();
                        if($server_accounts["status"] == true)
                        {
                            if(in_array($this->stream->get_adminusername(),$server_accounts["usernames"]) == true)
                            {
                                $acceptable_names = array();
                                $avname = explode(" ",strtolower($this->avatar->get_avatarname()));
                                $acceptable_names[] = $avname[0]; // Firstname
                                $acceptable_names[] = $avname[0]."_".substr($avname[1],0,2); // Firstname 2 letters of last name
                                $acceptable_names[] = $avname[0]."_".$this->stream->get_port(); // Firstname Port
                                $acceptable_names[] = $avname[0]."_".$this->stream->get_port()."_".$this->package->get_bitrate(); // Firstname Port Bitrate
                                $acceptable_names[] = $avname[0]."_".$this->stream->get_port()."_".$this->server->get_id(); // Firstname Port ServerID
                                $acceptable_names[] = $avname[0]."_".$this->rental->get_rental_uid(); // Firstname RentalUID
                                $accepted_name = "";
                                foreach($acceptable_names as $testname)
                                {
                                    if(in_array($testname,$server_accounts["usernames"]) == false)
                                    {
                                        $accepted_name = $testname;
                                        break;
                                    }
                                }
                                if(in_array($accepted_name,$acceptable_names) == true)
                                {
                                    $old_username = $this->stream->get_adminusername();
                                    $this->stream->set_field("adminusername",$accepted_name);
                                    $update_status = $this->stream->save_changes();
                                    if($update_status["status"] == true)
                                    {
                                        $status = $this->server_api->event_start_sync_username($this->stream,$this->server,$old_username);
                                        $this->message = $this->server_api->get_last_api_message();
                                        if($status == false)
                                        {
                                            $sql->flagError();
                                        }
                                    }
                                    else
                                    {
                                        $sql->flagError();
                                        $this->message = "failed to save changes to DB";
                                    }
                                }
                                else
                                {
                                    $this->message =  "no acceptable name found";
                                }
                            }
                            else
                            {
                                $this->message = "unable to find current account on server!";
                            }
                        }
                    }
                    else
                    {
                        // stream is up try and stop it then retry
                        $status = $this->server_api->opt_toggle_status($this->stream,$this->server,false);
                        if($status == true)
                        {
                            $status = false;
                            $this->message = "Unable to update username right now stopping server!";
                        }
                    }
                }
            }
        }
        return $status;
    }
}
?>
