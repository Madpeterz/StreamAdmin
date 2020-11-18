<?php

namespace YAPF\DbObjects\CollectionSet;

abstract class CollectionSetBulkRemove extends CollectionSetBulkUpdate
{
    /**
     * purgeCollection
     * Removes all objects from the database that are in the collection
     * @return mixed[] [status =>  bool,removed_entrys => integer, message =>  string]
     */
    public function purgeCollection(): array
    {
        $this->makeWorker();
        if ($this->getCount() == 0) {
            $error_msg = "Collection empty to start with";
            return ["status" => true, "removed_entrys" => 0, "message" => $error_msg];
        }
        $where_config = [
            "fields" => ["id"],
            "values" => [$this->getAllIds()],
            "types" => ["i"],
            "matches" => ["IN"],
        ];
        $remove_status = $this->sql->removeV2($this->getTable(), $where_config);
        $status = false;
        $removed_entrys = 0;

        $error_msg = "Failed to remove entrys from database because: " . $remove_status["message"];
        if ($remove_status["status"] == true) {
            $error_msg = "Incorrect number of entrys removed expected " . $this->getCount();
            $error_msg = " got " . $remove_status["rowsDeleted"];
            if ($remove_status["rowsDeleted"] == $this->getCount()) {
                $status = true;
                $error_msg = "ok";
                $removed_entrys = $remove_status["rowsDeleted"];
            }
        }
        return ["status" => $status, "removed_entrys" => $removed_entrys, "message" => $error_msg];
    }
}
