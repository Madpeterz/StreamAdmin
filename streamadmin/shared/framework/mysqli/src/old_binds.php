<?php
/*
    old binding logic due to be ripped out with something less shit.
    see how selectV2 does its logic so much nicer...
*/
abstract class mysqli_old_binds extends mysqli_count
{
    protected function bindParams($sql, $options = array(), $optionWord = "WHERE", $optionJoin = "AND")
    {
        if(count($options) > 0)
        {
            $sql .= " " . $optionWord;
            $addon = "";
            foreach($options as $optionValues)
            {
                foreach($optionValues as $key => $value)
                {
                    if($value == "LIKE_WILDCARD")
                    {
                        $sql .= $addon . " " . $key . " LIKE ?";
                    }
                    else
                    {
                        $sql .= $addon . " " . $key . $value . "?";
                    }
                    $addon = " " . $optionJoin;
                }
            }
        }
        return $sql;
    }
    protected function bindParamsMode2($sql, $options = array(), $startWord = "", $splitter = "", $endWord = "")
    {
        if(count($options) > 0)
        {
            $sql .= " " . $startWord;
            $addon = "";
            foreach($options as $optionsSets)
            {
                foreach($optionsSets as $key => $value)
                {
                    $sql .= $addon . " ?";
                    $addon = $splitter;
                }
            }
            $sql .= " " . $endWord;
        }
        return $sql;
    }
    protected function sql_bind(&$stmt, $values = array())
    {
        $args = array();
        if(count($values) > 0)
        {
            $bindText = "";
            $notFucked = true;
            foreach($values as $valueSets)
            {
                if(is_array($valueSets))
                {
                    foreach($valueSets as $key => $value)
                    {
                        $bindText .= $value;
                        $args[] = $key;
                    }
                }
                else
                {
                    $notFucked = false;
                    break;
                }
            }
            if($notFucked) mysqli_stmt_bind_param($stmt, $bindText, ...$args);
        }
        return $args;
    }
}

?>
