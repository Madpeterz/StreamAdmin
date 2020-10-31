<?php

namespace YAPF\DB_OBJECTS;

abstract class GenClass extends GenClassDB
{
    /**
     * HasAny
     * using a fast count query
     * check to see if there are ANY objects in the database
     * returns true if more than zero
     */
    public function hasAny(): bool
    {
        $reply = $this->sql->basic_count($this->get_table(), [["id" => ">"]], [[0 => "i"]]);
        if ($reply["status"] == true) {
            if ($reply["count"] > 0) {
                return true;
            }
        }
        return false;
    }
}
