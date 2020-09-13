<?php
	// Do not edit this file, rerun gen.php to update!
	class region_set extends collectionSet
	{
		public function get_object_by_field(string $fieldname,$value) : ?region
		{
			return parent::get_object_by_field($fieldname,$value);
		}
		public function get_object_by_id($id=null) : ?region
		{
			return parent::get_object_by_id($id);
		}
		public function get_first() : ?region
		{
			return parent::get_first();
		}
	}
	
	class region extends genClass
	{
		protected $use_table = "region";
		protected $dataset = array(
			"id" => array("type"=>"int","value"=>null),
			"name" => array("type"=>"str","value"=>null),
		);
		public function get_id() : ?int {  return $this->get_field("id");  } 
		public function get_name() : ?string {  return $this->get_field("name");  } 
		public function set_id(?int $newvalue) : array {  return $this->update_field("id",$newvalue);  } 
		public function set_name(?string $newvalue) : array {  return $this->update_field("name",$newvalue);  } 
	}
?>