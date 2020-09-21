<?php
	// Do not edit this file, rerun gen.php to update!
	class timezones_set extends collectionSet { }
	
	class timezones extends genClass
	{
		protected $use_table = "timezones";
		protected $dataset = array(
			"id" => array("type"=>"int","value"=>null),
			"name" => array("type"=>"str","value"=>null),
			"code" => array("type"=>"str","value"=>null),
		);
		public function get_name() : ?string {  return $this->get_field("name");  } 
		public function get_code() : ?string {  return $this->get_field("code");  } 
		public function set_name(?string $newvalue) : array {  return $this->update_field("name",$newvalue);  } 
		public function set_code(?string $newvalue) : array {  return $this->update_field("code",$newvalue);  } 
	}
?>