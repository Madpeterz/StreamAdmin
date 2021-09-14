<?php

namespace YAPF\DbObjects\CollectionSet;

abstract class CollectionSetBulk extends CollectionSetGet
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
            if ($this->cache != null) {
                $this->cache->markChangeToTable($this->getTable());
            }
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

    /**
     * updateFieldInCollection
     * Updates all objects in the collection's value
     * for the selected field
     * @return mixed[] [status =>  bool, changes => int, message =>  string]
     */
    public function updateFieldInCollection(string $update_field, $new_value): array
    {
        if ($this->disableUpdates == true) {
            return $this->addError(__FILE__, __FUNCTION__, "Attempt to update with limitFields enabled!");
        }
        return $this->updateMultipleFieldsForCollection([$update_field], [$new_value]);
    }
    /**
     * updateMultipleMakeUpdateConfig
     * processes the update_fields and new_values arrays into
     * a update config
     * used by: updateMultipleFieldsForCollection
     * @return mixed[] [status => bool, message => string, dataset => mixed[]
     * [fields => string[],values => mixed[], types => string[]]]
     */
    protected function updateMultipleMakeUpdateConfig(array $update_fields, array $new_values): array
    {
        $this->makeWorker();
        $update_config = [
            "fields" => [],
            "values" => [],
            "types" => [],
        ];
        $message = "ok";
        $all_ok = true;
        $loop = 0;
        while ($loop < count($update_fields)) {
            $lookup = "get" . ucfirst($update_fields[$loop]);
            if (method_exists($this->worker, $lookup) == false) {
                $all_ok = false;
                $message = "Unable to find getter: " . $lookup;
                break;
            }
            $field_type = $this->worker->getFieldType($update_fields[$loop], false);
            if ($field_type == null) {
                $all_ok = false;
                $message = "Unable to find fieldtype: " . $update_fields[$loop];
                break;
            }

            $update_config["fields"][] = $update_fields[$loop];
            $update_config["values"][] = $new_values[$loop];
            $update_config["types"][] = $this->worker->getFieldType($update_fields[$loop], true);
            $loop++;
        }
        return ["status" => $all_ok, "dataset" => $update_config, "message" => $message];
    }

    /**
     * updateMultipleGetUpdatedIds
     * using the fields that have changes
     * it builds an array of ids that need to have
     * the update applyed to them and the total number of
     * entrys that need to be updated
     * @return mixed[] [changes => integer,changed_ids => integer[]]
     */
    protected function updateMultipleGetUpdatedIds(array $update_fields, array $new_values): array
    {
        $expected_changes = 0;
        $changed_ids = [];
        $ids = $this->getAllIds();
        $total_update_fields = count($update_fields);
        foreach ($ids as $entry_id) {
            $localworker = $this->collected[$entry_id];
            $loop2 = 0;
            while ($loop2 < $total_update_fields) {
                $lookup = "get" . ucfirst($update_fields[$loop2]);
                if ($localworker->$lookup() != $new_values[$loop2]) {
                    $expected_changes++;
                    $changed_ids[] = $entry_id;
                    break;
                }
                $loop2++;
            }
        }
        return ["changes" => $expected_changes , "changed_ids" => $changed_ids];
    }
    /**
     * updateMultipleApplyChanges
     * applys the new values for each field to the collection
     */
    protected function updateMultipleApplyChanges(array $update_fields, array $new_values): void
    {
        $ids = $this->getallIds();
        $total_update_fields = count($update_fields);
        foreach ($ids as $entry_id) {
            $localworker = $this->collected[$entry_id];
            $loop2 = 0;
            while ($loop2 < $total_update_fields) {
                $applyer = "set" . ucfirst($update_fields[$loop2]);
                $localworker->$applyer($new_values[$loop2]);
                $loop2++;
            }
        }
    }
    /**
     * updateMultipleFieldsForCollection
     * using the fields and values updates the collection
     * and applys the changes to the database.
     * @return mixed[] [status =>  bool, changes => int, message =>  string]
     */
    public function updateMultipleFieldsForCollection(array $update_fields, array $new_values): array
    {
        if ($this->disableUpdates == true) {
            return $this->addError(__FILE__, __FUNCTION__, "Attempt to update with limitFields enabled!");
        }
        $this->makeWorker();
        if ($this->getCount() <= 0) {
            $error_msg = "Nothing loaded in collection";
            return ["status" => false, "changes" => 0, "message" => $error_msg];
        }
        if (count($update_fields) <= 0) {
            $error_msg = "No fields being updated!";
            return ["status" => false, "changes" => 0, "message" => $error_msg];
        }
        $ready_update_config = $this->updateMultipleMakeUpdateConfig($update_fields, $new_values);
        if ($ready_update_config["status"] == false) {
            $error_msg = $ready_update_config["message"];
            return ["status" => false, "changes" => 0, "message" => $error_msg];
        }
        $change_config = $this->updateMultipleGetUpdatedIds($update_fields, $new_values);
        if ($change_config["changes"] <= 0) {
            $error_msg = "No changes made";
            return ["status" => true, "changes" => 0, "message" => $error_msg];
        }
        $update_config = $ready_update_config["dataset"];
        $where_config = [
            "fields" => ["id"],
            "matches" => ["IN"],
            "values" => [$change_config["changed_ids"]],
            "types" => ["i"],
        ];
        $table = $this->worker->getTable();
        $total_changes = $change_config["changes"];
        unset($change_config);
        unset($ready_update_config);
        $update_status = $this->sql->updateV2($table, $update_config, $where_config, $total_changes);
        if ($update_status["status"] == true) {
            if ($this->cache != null) {
                $this->cache->markChangeToTable($this->getTable());
            }
            $this->updateMultipleApplyChanges($update_fields, $new_values);
            return $update_status;
        }
        $error_msg = "Update failed because:" . $update_status["message"];
        return ["status" => false, "changes" => 0, "message" => $error_msg];
    }
}
