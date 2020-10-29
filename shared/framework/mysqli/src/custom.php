<?php
abstract class mysqli_custom extends mysqli_old_binds
{
    public function search_tables(array $target_tables,string $match_field,$match_value,string $match_type="s",int $limit_amount=1,string $target_field="id") : array
    {
        if(count($target_tables) > 1)
        {
            $this->sqlStart();
            $bind_values = array();
            $sql = "";
            $addon = "";
            $table_id = 1;
            foreach($target_tables as $table)
            {
                $sql .= $addon;
                $sql .= "(SELECT tb".$table_id.".".$target_field.", '".$table."' AS source FROM ".$table." tb".$table_id."";
                $sql .= " WHERE tb".$table_id.".".$match_field." = ? ";
                if($limit_amount > 0) $sql .= "LIMIT ".$limit_amount.")";
                else $sql .= ")";
                $addon = " UNION ALL ";
                $bind_values[] = array($match_value => $match_type);
                $table_id++;
            }
            $sql .= " ORDER BY id DESC";
            echo $sql;
            if($stmt = $this->sqlConnection->prepare($sql))
            {
                $this->sql_bind($stmt, $bind_values,false);
                $stmt->execute();
                $result = $stmt->get_result();
                $dataSet = array();
                $loop = 0;
                while($entry = $result->fetch_assoc())
                {
                    $dataSet[$loop] = $entry;
                    $loop++;
                }
                return array("status"=>true, "dataSet"=>$dataSet ,"message" => "ok");
            }
            else  return $this->failure("Unable to prepare. " . $sql . "");
        }
        else return array("status"=>false,"message"=>"please use select if searching one table!");
    }

    /**
     * @deprecated selectV2 so much better
     */
    public function selectfromlist(string $table,array $fields,array $ids,string $fieldname,string $fieldtype) : array
    {
        $config = array();
        $config[] = array("table"=>$table);
        $config[] = array();
        $config[] = array(
             "fields"=>array($fieldname),
             "matches"=>array("IN"),
             "values"=>array($ids),
             "types"=>array($fieldtype),
         );
        return $this->selectV2(...$config);
    }

    /**
     * @deprecated due to be removed as its shit
     */
    public function custom_select($rawsql="", $values=array())
	{
        $this->sqlStart();
        if($stmt = mysqli_prepare($this->sqlConnection, $rawsql))
        {
            $this->sql_bind($stmt, $values);
			$stmt->execute();
	        $meta = $stmt->result_metadata();
	        $parameters = array();
	        while ($field = $meta->fetch_field())
	        {
	            $var = $field->name;
	            $$var = null;
	            $parameters[$var] = &$$var;
	        }
	        call_user_func_array(array($stmt, 'bind_result'), $parameters);
	        $dataSet = array();
	        while($stmt->fetch())
	        {
	            $c = array();
	            foreach($parameters as $key => $val)
	            {
	                $c[$key] = $val;
	            }
	            $dataSet[] = $c;
	        }
	        $stmt->close();
	        return array("status"=>true, "dataSet"=>$dataSet);
        }
        else return $this->failure("Incorrect SQL request [Unable to prepair]<br/>".$rawsql."");
	}
}
?>
