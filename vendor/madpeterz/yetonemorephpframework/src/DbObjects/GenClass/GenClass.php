<?php

namespace YAPF\DbObjects\GenClass;

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
        $where_config = [
            "fields" => ["id"],
            "values" => [-1],
            "types" => ["i"],
            "matches" => [">="],
        ];
        $reply = $this->sql->basicCountV2($this->getTable(), $where_config);
        if ($reply["status"] == true) {
            if ($reply["count"] > 0) {
                return true;
            }
        }
        return false;
    }

    public function makedisabled(): void
    {
        $this->disabled = true;
    }
}
