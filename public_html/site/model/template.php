<?php
	// Do not edit this file, rerun gen.php to update!
	class template_set extends collectionSet
	{
		public function get_object_by_field(string $fieldname,$value) : ?template
		{
			return parent::get_object_by_field($fieldname,$value);
		}
		public function get_object_by_id($id=null) : ?template
		{
			return parent::get_object_by_id($id);
		}
		public function get_first() : ?template
		{
			return parent::get_first();
		}
	}
	
	class template extends genClass
	{
		protected $use_table = "template";
		protected $dataset = array(
			"id" => array("type"=>"int","value"=>null),
			"name" => array("type"=>"str","value"=>null),
			"detail" => array("type"=>"str","value"=>null),
			"notecarddetail" => array("type"=>"str","value"=>null),
		);
		public function get_id() : ?int {  return $this->get_field("id");  } 
		public function get_name() : ?string {  return $this->get_field("name");  } 
		public function get_detail() : ?string {  return $this->get_field("detail");  } 
		public function get_notecarddetail() : ?string {  return $this->get_field("notecarddetail");  } 
		public function set_id(?int $newvalue) : array {  return $this->set_field("id",$newvalue);  } 
		public function set_name(?string $newvalue) : array {  return $this->set_field("name",$newvalue);  } 
		public function set_detail(?string $newvalue) : array {  return $this->set_field("detail",$newvalue);  } 
		public function set_notecarddetail(?string $newvalue) : array {  return $this->set_field("notecarddetail",$newvalue);  } 
	}
?>