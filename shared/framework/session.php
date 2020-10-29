<?php
class session_control extends error_logging
{
	protected $main_class_object = null;
	protected $logged_in = false;
	protected $session_values = array("lhash","autologout","nextcheck","username","ownerlevel");
	protected $lhash = 0;
	protected $autologout = 0;
	protected $nextcheck = 0;
	protected $username = "";
	protected $ownerlevel = 0;
	public function get_ownerlevel() : bool
	{
		if($this->ownerlevel == 1) return true;
		return false;
	}
	protected function populate_session_dataset() : bool
	{
		$this->lhash = $this->main_class_object->get_lhash();
		$this->autologout = time()+600;
		$this->nextcheck = time()+45;
		$this->username = $this->main_class_object->get_username();
		$this->ownerlevel = $this->main_class_object->get_ownerlevel();
		$this->update_session();
		return true;
	}
	public function end_session()
	{
		$this->why_logged_out = "Session ended";
		session_destroy();
	}
	protected $why_logged_out = "Not logged in at all";
	public function get_why_logged_out()
	{
		return $this->why_logged_out;
	}

	protected function create_lhash(bool $update_session_after=true) : bool
	{
		if($this->create_main_object() == true)
		{
			$new_lhash = $this->hash_password(time(),rand(1000,4000),microtime(),$this->main_class_object->get_lhash());
			$this->main_class_object->set_lhash($new_lhash);
			$this->nextcheck = time() + 120;
			$save_status = $this->main_class_object->save_changes();
			if($save_status["status"] == true)
			{
				$this->lhash = $new_lhash;
				if($update_session_after == true)
				{
					$this->update_session();
					global $sql;
					$sql->sqlSave();
				}
				return true;
			}
			else $this->why_logged_out = $save_status["message"];
		}
		else $this->why_logged_out = "Unable to create root session object";
		return false;
	}
	public function hash_password(string $arg1="",?string $arg2="",?string $arg3="",?string $arg4="",int $length=42) : string
	{
		$newhash = hash("sha256",implode("",array($arg1,$arg2,$arg3,$arg4)));
		if(strlen($newhash) > $length)
		{
			$newhash = substr($newhash,0,$length);
		}
		return $newhash;
	}
	protected function vaildate_lhash() : bool
	{
		if($this->create_main_object() == true)
		{
			if($this->lhash == $this->main_class_object->get_lhash())
			{
				return $this->create_lhash(true);
			}
			else $this->why_logged_out = "session lhash does not match db";
		}
		else $this->why_logged_out = "Unable to create root session object";
		return false;
	}
	protected function create_main_object(bool $also_load_object_from_session_lhash=true) : bool
	{
		if($this->main_class_object == null)
		{
			$this->main_class_object = new staff();
		}
		$load_ok = true;
		if($also_load_object_from_session_lhash == true)
		{
			if($this->main_class_object->get_id() == null)
			{
				$load_ok = $this->main_class_object->load_by_field("lhash",$this->lhash);
			}
		}
		return $load_ok;
	}
	protected function update_session()
	{
		foreach($this->session_values as $value)
		{
			$_SESSION[$value] = $this->$value;
		}
	}
	public function get_logged_in() : bool
	{
		return $this->logged_in;
	}
	public function load_from_session() : bool
	{
		if(isset($_SESSION))
		{
			$required_values_set = true;
			foreach($this->session_values as $value)
			{
				if(isset($_SESSION[$value]) == false)
				{
					$required_values_set = false;
					break;
				}
			}
			if($required_values_set == true)
			{
				foreach($this->session_values as $value)
				{
					$this->$value = $_SESSION[$value];
				}
				if($this->autologout > time())
				{
					$this->autologout = time() + 3600;
					$this->update_session();
					$this->logged_in = true;
					if($this->nextcheck < time())
					{
						$this->logged_in = $this->vaildate_lhash();
					}
					if($this->logged_in == false)
					{
						$this->end_session();
						$this->why_logged_out = "Session state error: Not logged in by session active";
					}
					return $this->logged_in;
				}
				else
				{
					$this->end_session();
					$this->why_logged_out = "Inactive auto logout";
				}
			}
			else $this->why_logged_out = "-";
		}
		else $this->why_logged_out = "-";
		return false;
	}
	public function update_password(string $new_password) : array
	{
		if($this->main_class_object != null)
		{
			$psalt = $this->hash_password(
				time(),
				$this->main_class_object->get_id(),
				$this->main_class_object->get_psalt(),
				$this->main_class_object->get_ownerlevel()
			);
			$phash = $this->hash_password(
				$new_password,
				$this->main_class_object->get_id(),
				$psalt,
				$this->main_class_object->get_ownerlevel()
			);
			$this->main_class_object->set_psalt($psalt);
			$this->main_class_object->set_phash($phash);
			return $this->main_class_object->save_changes();
		}
		else return array("status"=>false,"message"=>"update_password requires the user object to be loaded!");
	}
	public function userpassword_check(string $input_password) : bool
	{
		if($this->create_main_object(true) == true)
		{
			$expected_hash = null;
			if($expected_hash == null) $expected_hash = $this->main_class_object->get_phash();
			$check_hash = $this->hash_userpassword($input_password);
			if($check_hash["status"] == true)
			{
				if($check_hash["phash"] == $expected_hash)
				{
					return true;
				}
			}
		}
		return false;
	}
	public function hash_userpassword(string $input_password,bool $create_new_psalt=false) : array
	{
		if($this->main_class_object != null)
		{
			$p_salt = $this->main_class_object->get_psalt();
			if($create_new_psalt == true)
			{
				$p_salt = $this->hash_password(
					time(),
					$this->main_class_object->get_id(),
					$this->main_class_object->get_psalt(),
					$this->main_class_object->get_ownerlevel()
				);
			}
			return array(
			"status"=>true,
			"message"=>"hashed",
			"new_salt"=>$create_new_psalt,
			"salt_value" => $p_salt,
			"phash"=>$this->hash_password(
				$input_password,
				$this->main_class_object->get_id(),
				$p_salt,
				$this->main_class_object->get_ownerlevel()
				)
			);
		}
		else return array("status"=>false,"message"=>"hash_userpassword requires the user object to be loaded!");
	}
	public function attach_staff_member(staff $staff)
	{
		$this->main_class_object = $staff;
	}
	public function login_with_username_password(string $username,string $password) : bool
	{
		if($this->create_main_object(false) == true)
		{
			if($this->main_class_object->load_by_field("username",$username) == true)
			{
				if($this->userpassword_check($password) == true)
				{
					// login ok build session.
					return $this->populate_session_dataset();
				}
				else
				{
					$this->main_class_object = null; // remove link to that account
				}
			}
		}
		return false;
	}
}
?>
