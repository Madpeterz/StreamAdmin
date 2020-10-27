<?php
abstract class server_rest_api extends error_logging
{
    protected $stream = null;
    protected $server = null;
    protected $package = null;

    function __construct(stream $stream,server $server,package $package)
    {
        $this->stream = $stream;
        $this->server = $server;
        $this->package = $package;
    }
    protected function rest_request(string $method,string $endpoint,array $args=array()) : array
    {
        $address = $this->server->get_api_url()."".$endpoint;
        $client = new GuzzleHttp\Client();
        $headers = array(
            'Authorization' => 'Bearer ' . $server->get_api_password(),
            'Accept'        => 'application/json',
        );
        if(count($args) > 0)
        {
            $headers['form_params'] = array($args);
        }
        $res = $client->request($method,$address,$headers);
    }
    protected function rest_get(string $endpoint,array $args=array()) : array
    {
        return $this->rest_request("GET",$endpoint,$args);
    }
    protected function rest_post(string $endpoint,array $args=array()) : array
    {
        return $this->rest_request("POST",$endpoint,$args);
    }
    protected function rest_delete(string $endpoint,array $args=array()) : array
    {
        return $this->rest_request("DELETE",$endpoint,$args);
    }

}
abstract class server_api_protected extends server_rest_api
{
    protected $last_api_message = "";
    protected $needs_retry = false;
    protected function stream_state() : array
    {
        $this->last_api_message = "Skipped stream_state not supported on this api";
        return array("status"=>false,"state"=>false,"source"=>false);
    }
    protected function terminate_account(string $old_username)  : bool
    {
        $this->last_api_message = "Skipped terminate_account not supported on this api";
        return true;
    }
    protected function create_account() : bool
    {
        $this->last_api_message = "Skipped create_account not supported on this api";
        return true;
    }
    protected function dj_list() : array
    {
        $this->last_api_message = "Skipped dj_list not supported on this api";
        return array("status"=>true,"list"=>array());
    }
    protected function remove_dj(string $djaccount) : bool
    {
        $this->last_api_message = "Skipped remove_dj not supported on this api";
        return true;
    }
    protected function account_state() : array
    {
        $this->last_api_message = "Skipped account_state not supported on this api";
        return array("status"=>false,"state"=>false);
    }
    protected function account_name_list(bool $include_passwords=false,stream_set $stream_set=null) : array
    {
        return array("status"=>false,"usernames"=>array(),"message"=>"account_name_list supported on this api");
    }
    protected function sync_username(string $old_username) : bool
    {
        $this->last_api_message = "Skipped not supported on this api";
        return true;
    }
    protected function server_status() : array
    {
        return array("status"=>false,"loads"=>array("1"=>0,"5"=>0,"15"=>0),"ram"=>array("free"=>0,"max"=>0),"streams"=>array("total"=>0,"active"=>0),"message"=>"This api does not support server status");
    }
    protected function toggle_autodj() : bool
    {
        $this->last_api_message = "Skipped not supported on this api";
        return true;
    }
    protected function autodj_next() : bool
    {
        $this->last_api_message = "Skipped not supported on this api";
        return true;
    }
    protected function stop_server() : bool
    {
        $this->last_api_message = "Skipped not supported on this api";
        return true;
    }
    protected function start_server() : bool
    {
        $this->last_api_message = "Skipped not supported on this api";
        return true;
    }
    protected function susspend_server() : bool
    {
        $this->last_api_message = "Skipped not supported on this api";
        return true;
    }
    protected function un_susspend_server() : bool
    {
        $this->last_api_message = "Skipped not supported on this api";
        return true;
    }
    protected function change_password() : bool
    {
        $this->last_api_message = "Skipped not supported on this api";
        return true;
    }
    protected function change_title_now(string $newtitle) : bool
    {
        $this->last_api_message = "Skipped not supported on this api";
        return true;
    }

    protected function curl_request(string $url,array $post_data) : array
    {
        if(extension_loaded('curl') == true)
        {
            $post_dataset = "";
            $addon = "";
            foreach($post_data as $key => $value)
            {
                $post_dataset .= $addon;
                $post_dataset .= $key."=".$value;
                $addon = "&";
            }
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS,$post_dataset);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            $reply  = curl_exec($ch);
            $curl_error  = curl_error($ch);
            $curl_errno  = curl_errno($ch);
            if (is_resource($ch))
            {
                curl_close ($ch);
            }
            if ($curl_errno === 0)
            {
                return array("status"=>true,"message"=>$reply);
            }
            else
            {
                return array("status"=>false,"message"=>$curl_error);
            }
        }
        else
        {
            return array("status"=>false,"message"=>"Curl not enabled");
        }
    }
}
?>
