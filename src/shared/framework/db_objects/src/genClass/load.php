<?php

namespace YAPF\DB_OBJECTS;

abstract class GenClassLoad extends GenClassSet
{
    /**
     * loadTargeted E_USER_DEPRECATED
     * please use loadWithConfig
     */
    public function loadTargeted(array $wherefields, array $wherevalues, string $joinword = "AND"): bool
    {
        trigger_error("loadTargeted is being phased out please use loadWithConfig", E_USER_DEPRECATED);
        $whereconfig = [
            "join_with" => $joinword,
             "fields" => array_keys($wherefields),
             "matches" => array_values($wherefields),
             "values" => array_keys($wherevalues),
             "type" => array_values($wherevalues),
        ];
        return $this->loadWithConfig($whereconfig);
    }
    /**
     * loadOnField
     * alias of loadByField
     * loads a object that matchs in the DB on the field and value
     */
    public function loadOnField(string $field_name, string $field_value): bool
    {
        return $this->loadByField($field_name, $field_value);
    }
    /**
     * loadByField
     * loads a object that matchs in the DB on the field and value
     */
    public function loadByField(string $field_name, string $field_value): bool
    {
        $field_type = $this->getFieldType($field_name, true);
        if ($field_type !== null) {
            $whereconfig = [
                 "fields" => [$field_name],
                 "matches" => ["="],
                 "values" => [$field_value],
                 "types" => [$field_type],
            ];
            return $this->loadWithConfig($whereconfig);
        }
        $this->addError("Attempted to get field type: " . $field_name . " but its not supported!");
        return false;
    }
    /**
     * load E_USER_DEPRECATED
     * Please use loadID
     */
    public function load(integer $id): bool
    {
        trigger_error("load is being phased out please use loadID", E_USER_DEPRECATED);
        return $this->loadID($id);
    }
    /**
     * loadID
     * loads the object from the database that matchs the id
     */
    public function loadID(integer $id): bool
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
            $options_config = [
                "page_number" => 0,
                "max_entrys" => 1,
            ];
            $basic = ["table" => $this->get_table()];
            $load_data = $this->sql->selectV2($basic, null, $whereconfig, $options_config);
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
            if (count($load_data["dataSet"]) == 1) {
                $id_check_passed = true;
                $this->setup($load_data["dataSet"][0]);
                if (REQUIRE_ID_ON_LOAD == true) {
                    if ($this->getID() <= 0) {
                        $id_check_passed = false;
                    }
                }
                return $id_check_passed;
            }
            $error_message = "Attempt to load multiple entrys into solo storage,";
            $error_message .= " please use the set collector X_set found: ";
            $error_message .= count($load_dat["dataSet"]) . " matches only allowed 1";
            $this->addError(__FILE__, __FUNCTION__, $error_message);
        }
        return false;
    }
}
