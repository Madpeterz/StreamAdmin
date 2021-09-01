<?php

namespace YAPF\DbObjects\GenClass;

use YAPF\Cache\Cache;

abstract class GenClassSet extends GenClassGet
{
    protected ?Cache $cache = null;
    protected bool $cacheAllowChanged = false;

    public function attachCache(Cache $forceAttach): void
    {
        $this->cache = $forceAttach;
    }
    public function setCacheAllowChanged(bool $status = true): void
    {
        $this->cacheAllowChanged = $status;
    }
    protected bool $expectedSqlLoadError = false;

    public function expectedSqlLoadError(bool $setFlag = false): void
    {
        $this->expectedSqlLoadError = $setFlag;
    }
    public function setBadId(): void
    {
        $this->bad_id = true;
    }
    public function disableAllowSetField(): void
    {
        $this->allow_set_field = false;
    }
    /**
     * __construct
     * [Optional] takes a key => value array
     * where the key is the field
     * and sets on the object with these values,
     * you should avoid this and use the load[X] methods!
     */
    public function __construct(array $defaults = [])
    {
        global $cache;
        if (count($defaults) > 0) {
            $this->setup($defaults);
        }
        if (isset($cache) == true) {
            $this->cache = $cache;
        }
        parent::__construct();
    }
    /**
     * setup
     * Fills in the dataset with a key => value array
     * used when first loading a object
     * returns true if there was no errors
     * unknown keys are skipped
     */
    public function setup(array $keyvalues): bool
    {
        $hasErrors = false;
        $saveDataset = $this->dataset;
        foreach ($keyvalues as $key => $value) {
            if (array_key_exists($key, $this->dataset) == false) {
                continue;
            }
            $this->dataset[$key]["value"] = $value;
        }
        if ($hasErrors == true) {
            $this->dataset = $saveDataset;
            return false;
        }
        $this->save_dataset = $this->dataset;
        return true;
    }
    /**
     * setId
     * force sets the Id of a object, please avoid using this!
     * @return mixed[] [status =>  bool, message =>  string]
     */
    public function setId(int $newvalue): array
    {
        if ($this->bad_id == false) {
            $this->addError(__FILE__, __FUNCTION__, "Warning: setId called. if you expected this please ignore");
            return $this->updateField($this->use_id_field, $newvalue, true);
        }
        return ["status" => false,"message" => "bad_id flag is set unable to setId"];
    }
    /**
     * setTable
     * Sets the table used by the object
     * note: You should avoid using this unless you know
     * what your doing
     */
    public function setTable(string $tablename = ""): void
    {
        $this->addError(__FILE__, __FUNCTION__, "Warning: setTable called. if you expected this please ignore");
        $this->use_table = $tablename;
    }
    /**
     * updateField
     * Updates the live value of a object
     * call 'saveChanges' to apply the changes to the DB!
     * Note: Setting the ID can lead to weird side effects!
     * @return mixed[] [status =>  bool, message =>  string]
     */
    protected function updateField(string $fieldname, $value, bool $ignore_set_id_warning = false): array
    {
        if (count($this->dataset) != count($this->save_dataset)) {
            $this->save_dataset = $this->dataset;
        }
        if (is_object($value) == true) {
            $errored_on = "System error: Attempt to put a object onto field: " . $fieldname;
            return $this->addError(__FILE__, __FUNCTION__, $errored_on);
        }
        if (is_array($value) == true) {
            $errored_on = "System error: Attempt to put a array onto field: " . $fieldname;
            return $this->addError(__FILE__, __FUNCTION__, $errored_on);
        }
        if ($this->disabled == true) {
            $errored_on = "This class is disabled";
            return $this->addError(__FILE__, __FUNCTION__, $errored_on);
        }
        if ($this->allow_set_field == false) {
            $errored_on = "update_field is not allowed for this object";
            return $this->addError(__FILE__, __FUNCTION__, $errored_on);
        }
        if (array_key_exists($fieldname, $this->dataset) == false) {
            $errored_on = "Sorry this object does not have the field: " . $fieldname;
            return $this->addError(__FILE__, __FUNCTION__, $errored_on);
        }
        if (($fieldname == "id") && ($ignore_set_id_warning == false)) {
            $errored_on = "Sorry this object does not allow you to set the id field!";
            return $this->addError(__FILE__, __FUNCTION__, $errored_on);
        }
        $this->dataset[$fieldname]["value"] = $value;
        if ($this->getFieldType($fieldname) == "bool") {
            $this->dataset[$fieldname]["value"] = 0;
            if (in_array($value, [1, "1", "true", true, "yes"], true) == true) {
                $this->dataset[$fieldname]["value"] = 1;
            }
        }
        return ["status" => true, "message" => "value set"];
    }
}
