<?php
abstract class genClass extends genClass_db
{
    public function HasAny() : bool
    {
        $reply = $this->sql->basic_count($this->get_table(),array(array("id"=>">")),array(array(0 => "i")));
        if($reply["status"] == true)
        {
            if($reply["count"] > 0)
            {
                return true;
            }
        }
        return false;
    }
}
?>
