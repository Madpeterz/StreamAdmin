<?php
abstract class genClass_collection_load extends genClass_collection_removebulk
{
    public function load_ids(array $ids,string $fieldname="id",string $fieldtype="i",bool $ids_clean=true) : array
    {
        return $this->loadDataFromList($fieldname,$ids,$ids_clean);
    }
	public function load_on_fields(array $fields,array $fieldvalues,array $matchtypes,string $merge_word="AND") : array
	{
		if($this->worker_class != null)
		{
        	$worker = new $this->worker_class();
			$wherefields = array();
			$wherevalues = array();
			$unpack_ok = true;
			$unpack_error = "";
			$loop = 0;
			while($loop < count($fields))
			{
				$fieldname = $fields[$loop];
				if(method_exists($worker,"get_".$fieldname))
				{
					$field_type = $worker->get_field_type($fieldname,true);
					if($field_type != null)
					{
						$wherefields[] = array($fieldname => $matchtypes[$loop]);
						$wherevalues[] = array($fieldvalues[$loop] => $field_type);
					}
					else
					{
						$unpack_ok = false;
						$unpack_error = "get_".$fieldname." fieldtype is not supported!";
						break;
					}
				}
				else
				{
					$unpack_ok = false;
					$unpack_error = "get_".$fieldname." is not supported on worker";
					break;
				}
				$loop++;
			}
			if($unpack_ok == true)
			{
				return $this->loadData($wherefields,$wherevalues,$merge_word);
			}
			else
			{
				$this->addError(__FILE__,__FUNCTION__,$unpack_error);
				return array("status"=>false,"count"=>0,"message"=>$unpack_error);
			}
		}
		else
		{
			$this->addError(__FILE__,__FUNCTION__,"Worker not setup");
			return array("status"=>false,"count"=>0,"message"=>"worker not setup");
		}
	}
    public function load_by_field(string $fieldname,$fieldvalue,int $force_limit=0,string $order_by="id",string $order_dir="DESC") : array
    {
        return $this->load_on_field($fieldname,$fieldvalue,$force_limit,$order_by,$order_dir);
    }
    public function load_on_field(string $fieldname,$fieldvalue,int $force_limit=0,string $order_by="id",string $order_dir="DESC") : array
    {
		if($this->worker_class != null)
		{
        		$worker = new $this->worker_class();
        		if(method_exists($worker,"get_".$fieldname))
        		{
						$field_type = $worker->get_field_type($fieldname,true);
						if($field_type != null)
						{
		            		$wherefields = array(array($fieldname => "="));
		            		$wherevalues = array(array($fieldvalue => $field_type));
		            		return $this->loadData($wherefields,$wherevalues,"AND",$order_by,$order_dir,$force_limit);
						}
						else
						{
							$this->addError(__FILE__,__FUNCTION__,"Unable to find fieldtype to load with");
							return array("status"=>false,"count"=>0,"message"=>"Unable to find fieldtype to load with");
						}
        		}
				else
				{
					$this->addError(__FILE__,__FUNCTION__,"".$fieldname." is not a supported field");
					return array("status"=>false,"count"=>0,"message"=>"not a supported field");
				}
		}
		else
		{
			$this->addError(__FILE__,__FUNCTION__,"Worker not setup");
			return array("status"=>false,"count"=>0,"message"=>"worker not setup");
		}
    }
	public function load_limited($limit=12,$by_field="id",$by_direction="ASC",$wherefields=array(),$wherevalues=array(),$merge_word="AND",$page=0)
	{
		return $this->loadData($wherefields,$wherevalues,$merge_word,$by_field,$by_direction,$limit,$page);
	}
	public function load_newest($limit=12,$wherefields=array(),$wherevalues=array(),$by_field="id",$by_direction="DESC",$page=0)
	{
		return $this->load_limited($limit,$by_field,$by_direction,$wherefields,$wherevalues,"AND",$page);
	}
    public function loadAll($limit=0,$order_by="id",$by_direction="ASC")
    {
        return $this->loadData(array(),array(),"AND",$order_by,$by_direction,$limit,0);
    }
    public function load_with_config(?array $where_config=array(),?array $order_config=null,?array $options_config=null,?array $join_tables=null)
    {
        if($this->worker_class != null)
        {
            $worker = new $this->worker_class();
            $read_from_table = $worker->get_table();
            $new_object = new $this->worker_class();

            $load_data = $this->sql->selectV2(
                array("table"=>$read_from_table),
                $order_config,
                $where_config,
                $options_config,
                $join_tables
            );
            if($load_data["status"] == true)
            {
                return $this->process_load($load_data);
            }
            else
            {
                print_r($load_data);
                $this->addError(__FILE__, __FUNCTION__, "".get_class($this)." Unable to load data: ".$load_data["message"]."");
                return array("status"=>false,"count"=>0,"message"=>"Unable to load data ".$load_data["message"]."");
            }
        }
        else
        {
            $this->addError(__FILE__, __FUNCTION__, "".get_class($this)." Required worker_class not set!");
            return array("status"=>false,"count"=>0,"message"=>"Required worker_class not set!");
        }
    }
    protected function loadData($wherefields=array(), $wherevalues=array(), $joinword="AND", $orderBy="", $orderDir="DESC", $limit=0, $page=0)
    {
        if($this->worker_class != null)
        {
            $worker = new $this->worker_class();
            $read_from_table = $worker->get_table();
            $load_data = $this->sql->select($read_from_table,null,$wherefields,$wherevalues,$joinword,$orderBy,$orderDir,$limit,$page);
            if($load_data["status"])
            {
                return $this->process_load($load_data);
            }
            else
            {
                $this->addError(__FILE__, __FUNCTION__, "".get_class($this)." Unable to load data: ".$load_data["message"]."");
                return array("status"=>false,"count"=>0,"message"=>"Unable to load data, ".$load_data["message"]."");
            }
        }
        else
        {
            $this->addError(__FILE__, __FUNCTION__, "".get_class($this)." Required worker_class not set!");
            return array("status"=>false,"count"=>0,"message"=>"Required worker_class not set!");
        }
    }
    protected function loadDataFromList(string $fieldname="id",array $ids=array(),$ids_clean=true) : array
    {
        if($this->worker_class != null)
        {
            $uids = array();
            // do we trust the list of ids to be clean
            if($ids_clean == false)
            {
                foreach($ids as $id)
                {
                    if(in_array($id,$uids) == false)
                    {
                        $uids[] = $id;
                    }
                }
            }
            else
            {
                $uids = $ids;
            }
            $ids = array();
            $worker = new $this->worker_class();
            $read_from_table = $worker->get_table();
            $new_object = new $this->worker_class();

            return $this->load_with_config(array(
                "fields"=>array($fieldname),
                "matches"=>array("IN"),
                "values"=>array($uids),
                "types"=>array($new_object->get_field_type($fieldname,true))
                )
            );
        }
        else
        {
            $this->addError(__FILE__, __FUNCTION__, "".get_class($this)." Required worker_class not set!");
            return array("status"=>false,"count"=>0,"message"=>"Required worker_class not set!");
        }
    }
}
?>
