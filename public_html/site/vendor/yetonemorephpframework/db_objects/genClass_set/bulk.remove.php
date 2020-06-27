<?php
abstract class genClass_collection_removebulk  extends genClass_collection_updatebulk
{
    public function purge_collection_set()
	{
		$fail_message = "";
		if($this->get_count() > 0)
		{
			if($this->worker_class != null)
			{
				$test_object = new $this->worker_class();
				$wherefields = array();
				$werevalues = array();
				foreach($this->get_all_ids() as $object_id)
				{
					$wherefields[] = array("id"=>"=");
					$wherevalues[] = array($object_id=>"i");
				}
				$remove_status = $this->sql->remove($test_object->get_table(),$wherefields,$wherevalues,"OR");
				if($remove_status["status"] == true)
				{
					if($remove_status["rowsDeleted"] == $this->get_count())
					{
						$this->collected = array();
						return array("status"=>true,"message"=>"collection purged");
					}
					else $fail_message = "Incorrect number of entrys removed: expected ".$this->get_count()." got ".$remove_status["rowsDeleted"]."";
				}
				else $fail_message = "Failed to remove entrys from database";
			}
			else $fail_message = "worker_class not set";
			return array("status"=>false,"message"=>$fail_message);
		}
		else
		{
			return array("status"=>true,"removed_entrys"=>0,"message"=>"no entrys removed");
		}
	}
}
?>
