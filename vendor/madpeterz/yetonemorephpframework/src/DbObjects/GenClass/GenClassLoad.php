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
        if (is_object($field_value) == true) {
            $errormsg = "Attempted to pass field_value as a object!";
            $this->addError(__FILE__, __FUNCTION__, $errormsg);
            return false;
        }
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
        if ($this->disabled == true) {
            $this->addError(__FILE__, __FUNCTION__, "unable to loadData this class is disabled");
            return false;
        }

        // Cache support
        $hitCache = false;
        $hashme = "";
        if ($this->cache != null) {
            $hashme = $this->cache->getHash(
                $whereconfig,
                ["single" => true],
                ["single" => true],
                ["single" => true],
                $this->getTable(),
                count($this->getFields())
            );
            $hitCache = $this->cache->cacheVaild($this->getTable(), $hashme, true);
        }
        if ($hitCache == true) {
            // wooo vaild data from cache!
            return $this->processLoad($this->cache->readHash($this->getTable(), $hashme));
        }
        $basic = ["table" => $this->getTable()];
        $this->sql->setExpectedErrorFlag($this->expectedSqlLoadError);
        $load_data = $this->sql->selectV2($basic, null, $whereconfig);
        $this->sql->setExpectedErrorFlag(false);
        if ($this->cache != null) {
            // push data to cache so we can avoid reading from DB as much
            $this->cache->writeHash($this->getTable(), $hashme, $load_data, $this->cacheAllowChanged);
        }
        return $this->processLoad($load_data);
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
                if (defined("REQUIRE_ID_ON_LOAD") == false) {
                    $this->addError(__FILE__, __FUNCTION__, "REQUIRE_ID_ON_LOAD define is missing");
                    return false;
                }
                if (REQUIRE_ID_ON_LOAD == true) {
                    if (($this->getId() <= 0) || ($this->getId() === null)) {
                        $id_check_passed = false;
                        $this->dataset = $restore_dataset;
                    }
                }
                return $id_check_passed;
            }
            if (count($load_data["dataset"]) > 1) {
                $error_message = "Load error incorrect number of entrys expected 1 but got:";
                $error_message .= count($load_data["dataset"]);
                $this->addError(__FILE__, __FUNCTION__, $error_message);
            }
        }
        return false;
    }
}
