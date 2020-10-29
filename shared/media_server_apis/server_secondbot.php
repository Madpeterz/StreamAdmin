<?php
class server_secondbot extends server_secondbot_core
{
    protected function process_api_call(server $server,array $args) : array
    {
        $post_data = array();
        $post_data["token"] = sha1(time()."eventinbound".$server->get_api_password()."");
        $url = $server->get_api_url()."/event/inbound";
        foreach($args as $key => $value)
        {
            $post_data[$key] = $value;
        }
        $reply = $this->curl_request($url,$post_data);
        if($reply["status"] == true)
        {
            $this->last_api_message = "curl ok";
            return array("status"=>true,"data"=>json_decode($reply["message"], true));
        }
        else
        {
            $this->last_api_message = "curl failed with message: ".$reply["message"]."";
            return array("status"=>false,"data"=>array());
        }
    }
    protected function clear_event(event $event)
    {
        $remove_status = $event->remove_me();
        if($remove_status["status"] == true)
        {
            $reply = array("status"=>true,"message"=>"event_removed");
        }
        else
        {
            $reply = array("status"=>false,"message"=>"unable to remove event: ".$remove_status["message"]."");
        }
    }
    public function next_event()
    {
        global $reply, $sql;
        $server = new server();
        $server->load_by_field("apilink",5);
        $event_set = new event_set();
        $event_set->load_newest(1,array(),array(),"id","ASC");
        if($event_set->get_count() == 1)
        {
            $event = $event_set->get_first();
            if($event != null)
            {
                $postargs = array();
                foreach($event->get_fields() as $field)
                {
                    $postargs[$field] = $event->get_field($field);
                }
                $clear_reply = $this->clear_event($event);
                if($clear_reply["status"] == true)
                {
                    $reply = $this->process_api_call($server,$postargs);
                    if($reply["status"] == true)
                    {
                        if(array_key_exists("action",$reply["data"]) == true)
                        {
                            if($reply["data"]["action"] != "clear")
                            {
                                $sql->flagError();
                                $reply = array("status"=>false,"message"=>"Reply action is not clear: ".$reply["message"]."");
                            }
                        }
                        else
                        {
                            $reply = array("status"=>false,"message"=>"no reply action!");
                            $sql->flagError();
                        }
                    }
                    else
                    {
                        $reply = array("status"=>false,"message"=>$reply["message"]);
                    }
                }
                else
                {
                    return $clear_reply;
                }
            }
            else
            {
                $reply = array("status"=>false,"message"=>"cant get event");
            }
        }
        else
        {
            $reply = array("status"=>true,"message"=>"nowork");
        }
    }
}
?>
