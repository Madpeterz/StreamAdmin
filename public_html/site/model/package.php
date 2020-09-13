<?php
	// Do not edit this file, rerun gen.php to update!
	class package_set extends collectionSet
	{
		public function get_object_by_field(string $fieldname,$value) : ?package
		{
			return parent::get_object_by_field($fieldname,$value);
		}
		public function get_object_by_id($id=null) : ?package
		{
			return parent::get_object_by_id($id);
		}
		public function get_first() : ?package
		{
			return parent::get_first();
		}
	}
	
	class package extends genClass
	{
		protected $use_table = "package";
		protected $dataset = array(
			"id" => array("type"=>"int","value"=>null),
			"package_uid" => array("type"=>"str","value"=>null),
			"name" => array("type"=>"str","value"=>null),
			"autodj" => array("type"=>"bool","value"=>0),
			"autodj_size" => array("type"=>"str","value"=>null),
			"listeners" => array("type"=>"int","value"=>null),
			"bitrate" => array("type"=>"int","value"=>null),
			"templatelink" => array("type"=>"int","value"=>null),
			"cost" => array("type"=>"int","value"=>null),
			"days" => array("type"=>"int","value"=>null),
			"texture_uuid_soldout" => array("type"=>"str","value"=>null),
			"texture_uuid_instock_small" => array("type"=>"str","value"=>null),
			"texture_uuid_instock_selected" => array("type"=>"str","value"=>null),
		);
		public function get_id() : ?int {  return $this->get_field("id");  } 
		public function get_package_uid() : ?string {  return $this->get_field("package_uid");  } 
		public function get_name() : ?string {  return $this->get_field("name");  } 
		public function get_autodj() : ?bool {  return $this->get_field("autodj");  } 
		public function get_autodj_size() : ?string {  return $this->get_field("autodj_size");  } 
		public function get_listeners() : ?int {  return $this->get_field("listeners");  } 
		public function get_bitrate() : ?int {  return $this->get_field("bitrate");  } 
		public function get_templatelink() : ?int {  return $this->get_field("templatelink");  } 
		public function get_cost() : ?int {  return $this->get_field("cost");  } 
		public function get_days() : ?int {  return $this->get_field("days");  } 
		public function get_texture_uuid_soldout() : ?string {  return $this->get_field("texture_uuid_soldout");  } 
		public function get_texture_uuid_instock_small() : ?string {  return $this->get_field("texture_uuid_instock_small");  } 
		public function get_texture_uuid_instock_selected() : ?string {  return $this->get_field("texture_uuid_instock_selected");  } 
		public function set_id(?int $newvalue) : array {  return $this->set_field("id",$newvalue);  } 
		public function set_package_uid(?string $newvalue) : array {  return $this->set_field("package_uid",$newvalue);  } 
		public function set_name(?string $newvalue) : array {  return $this->set_field("name",$newvalue);  } 
		public function set_autodj(?bool $newvalue) : array {  return $this->set_field("autodj",$newvalue);  } 
		public function set_autodj_size(?string $newvalue) : array {  return $this->set_field("autodj_size",$newvalue);  } 
		public function set_listeners(?int $newvalue) : array {  return $this->set_field("listeners",$newvalue);  } 
		public function set_bitrate(?int $newvalue) : array {  return $this->set_field("bitrate",$newvalue);  } 
		public function set_templatelink(?int $newvalue) : array {  return $this->set_field("templatelink",$newvalue);  } 
		public function set_cost(?int $newvalue) : array {  return $this->set_field("cost",$newvalue);  } 
		public function set_days(?int $newvalue) : array {  return $this->set_field("days",$newvalue);  } 
		public function set_texture_uuid_soldout(?string $newvalue) : array {  return $this->set_field("texture_uuid_soldout",$newvalue);  } 
		public function set_texture_uuid_instock_small(?string $newvalue) : array {  return $this->set_field("texture_uuid_instock_small",$newvalue);  } 
		public function set_texture_uuid_instock_selected(?string $newvalue) : array {  return $this->set_field("texture_uuid_instock_selected",$newvalue);  } 
	}
?>