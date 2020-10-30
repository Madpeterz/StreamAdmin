<?php
abstract class genClass_collection_removebulk  extends genClass_collection_updatebulk
{
    public function purge_collection_set() : array
	{
		if($this->get_count() == 0)
		{
            return array("status"=>true,"removed_entrys"=>0,"message"=>"Collection empty to start with");
        }
        else if($this->worker_class == null)
        {
            return array("status"=>false,"removed_entrys"=>0,"message"=>"worker_class not set");
        }

		$test_object = new $this->worker_class();
		$wherefields = [];
		$werevalues = [];
		foreach($this->get_all_ids() as $object_id)
		{
			$wherefields[] = array("id"=>"=");
			$wherevalues[] = array($object_id=>"i");
		}

		$remove_status = $this->sql->remove($test_object->get_table(),$wherefields,$wherevalues,"OR");
        if($remove_status["status"] == false)
        {
            return array("status"=>false,"removed_entrys"=>0,"message"=>"Failed to remove entrys from database because: ".$remove_status["message"]);
        }
        else if($remove_status["rowsDeleted"] != $this->get_count())
        {
            return array("status"=>false,"removed_entrys"=>$remove_status["rowsDeleted"],"message"=>"Incorrect number of entrys removed expected ".$this->get_count()." got ".$remove_status["rowsDeleted"]);
        }
        return array("status"=>true,"removed_entrys"=>$remove_status["rowsDeleted"],"message"=>"collection purged");
	}
}
?>
