<?php

namespace YAPF\DB_OBJECTS;

use YAPF\SqlConnectedClass as SqlConnectedClass;

abstract class CollectionSetBulkRemove extends CollectionSetBulkUpdate
{
    /**
     * purgeCollectionSet [E_USER_DEPRECATED]
     * please use: purgeCollection
     * Removes all objects from the database that are in the collection
     * @return mixed[] [status =>  bool,removed_entrys => integer, message =>  string]
     */
    public function purgeCollectionSet(): array
    {
        $errormsg = "purgeCollectionSet is being phased out please use purgeCollection";
        trigger_error($errormsg, E_USER_DEPRECATED);
        return $this->purgeCollection();
    }
    /**
     * purgeCollection
     * Removes all objects from the database that are in the collection
     * @return mixed[] [status =>  bool,removed_entrys => integer, message =>  string]
     */
    public function purgeCollection(): array
    {
        $this->makeWorker();
        if ($this->get_count() == 0) {
            $error_msg = "Collection empty to start with";
            return ["status" => true, "removed_entrys" => 0, "message" => $error_msg];
        }
        $wherefields = [];
        $werevalues = [];
        foreach ($this->get_all_ids() as $object_id) {
            $wherefields[] = ["id" => "="];
            $wherevalues[] = [$object_id => "i"];
        }
        $remove_status = $this->sql->remove($this->worker->get_table(), $wherefields, $wherevalues, "OR");
        $status = false;
        $removed_entrys = 0;
        $error_msg = "Collection purged";
        if ($remove_status["status"] == false) {
            $error_msg = "Failed to remove entrys from database because: " . $remove_status["message"];
        } elseif ($remove_status["rowsDeleted"] != $this->get_count()) {
            $error_msg = "Incorrect number of entrys removed expected " . $this->get_count();
            $error_msg = " got " . $remove_status["rowsDeleted"];
        } else {
            $status = true;
            $removed_entrys = $remove_status["rowsDeleted"];
        }
        return ["status" => status, "removed_entrys" => $removed_entrys, "message" => $error_msg];
    }
}
