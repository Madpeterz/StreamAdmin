<?php

namespace YAPF\DbObjects\GenClass;

abstract class GenClassLoad extends GenClassSet
{
    /**
     * loadOnField
     * alias of loadByField
     * loads a object that matchs in the DB on the field and value
     */
    public function loadOnField(string $field_name, $field_value): bool
    {
        return $this->loadByField($field_name, $field_value);
    }
    /**
     * loadByField
     * loads a object that matchs in the DB on the field and value
     */
    public function loadByField(string $field_name, $field_value): bool
    {
        $field_type = $this->getFieldType($field_name, true);
        if ($field_type == null) {
            $errormsg = "Attempted to get field type: " . $field_name . " but its not supported!";
            $this->addError(__FILE__, __FUNCTION__, $errormsg);
            return false;
        }
        $whereconfig = [
                "fields" => [$field_name],
                "matches" => ["="],
                "values" => [$field_value],
                "types" => [$field_type],
        ];
        return $this->loadWithConfig($whereconfig);
    }
    /**
     * loadID
     * loads the object from the database that matchs the id
     */
    public function loadID(int $id): bool
    {
        $whereconfig = [
             "fields" => ["id"],
             "matches" => ["="],
             "values" => [$id],
             "types" => ["i"],
        ];
        return $this->loadWithConfig($whereconfig);
    }

    /**
     * loadWithConfig
     * Fetchs data from the DB and hands it over to processLoad
     * where it matchs the whereconfig.
     * returns false if the class is disabled or the load fails
     */
    public function loadWithConfig(array $whereconfig): bool
    {
        if ($this->disabled == false) {
            $basic = ["table" => $this->getTable()];
            $load_data = $this->sql->selectV2($basic, null, $whereconfig);
            return $this->processLoad($load_data);
        }
        $this->addError(__FILE__, __FUNCTION__, "unable to loadData this class is disabled");
        return false;
    }
    /**
     * processLoad
     * takes the result of the mysqli select
     * and fills in the objects dataset
     * returns true if needed checks are passed
     */
    protected function processLoad(array $load_data): bool
    {
        if ($load_data["status"] == true) {
            if (count($load_data["dataset"]) == 1) {
                $id_check_passed = true;
                $restore_dataset = $this->dataset;
                $this->setup($load_data["dataset"][0]);
                if (REQUIRE_ID_ON_LOAD == true) {
                    if (($this->getId() <= 0) || ($this->getId() === null)) {
                        $id_check_passed = false;
                        $this->dataset = $restore_dataset;
                    }
                }
                return $id_check_passed;
            }
            $error_message = "Load error incorrect number of entrys expected 1 but got:";
            $error_message .= count($load_data["dataset"]);
            $this->addError(__FILE__, __FUNCTION__, $error_message);
        }
        return false;
    }
}
