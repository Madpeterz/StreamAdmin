<?php

namespace YAPF\DbObjects\GenClass;

use Exception;
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

    /*
        bulkChange
        takes in a name => value pairs as an array
        passes that to the set function

        on failure rolls back any changes to the object
    */
    public function bulkChange(array $namevaluepairs): bool
    {
        $rollback_savedataset = $this->save_dataset;
        $rollback_dataset = $this->dataset;
        $all_ok = true;
        $why_failed = "";
        try {
            foreach ($namevaluepairs as $key => $value) {
                $functionname = "set" . ucfirst($key);
                if (method_exists($this, $functionname) == false) {
                    $why_failed = "Unknown key " . $key;
                    $all_ok = false;
                    break;
                }
                $status = $this->$functionname($value);
                if (is_array($status) == false) {
                    $why_failed = "reply from function " . $functionname . " should be an array";
                    $all_ok = false;
                    break;
                }
                if ($status["status"] == false) {
                    $why_failed = $status["message"];
                    $all_ok = false;
                    break;
                }
            }
        } catch (Exception $e) {
            $why_failed = $e->getMessage();
            $all_ok = false;
        }
        if ($all_ok == false) {
            $this->addError(__FILE__, __FUNCTION__, $why_failed);
            $this->save_dataset = $rollback_savedataset;
            $this->dataset = $rollback_dataset;
        }
        return $all_ok;
    }

    /*
        defaultValues

        forces the object to return to default values for all
        fields apart from id and any excluded fields

        on failure rolls back any changes to the object
    */
    public function defaultValues(array $excludeFields = []): bool
    {
        $rollback_savedataset = $this->save_dataset;
        $rollback_dataset = $this->dataset;
        $excludeFields[] = "id";
        $class = get_class($this);
        $copy = new $class();
        $fields = $this->getFields();
        $all_ok = true;
        $why_failed = "";
        foreach ($fields as $field) {
            if (in_array($field, $excludeFields) == true) {
                continue;
            }
            $functionnameset = "set" . ucfirst($field);
            $functionnameget = "get" . ucfirst($field);
            if (method_exists($this, $functionnameset) == false) {
                $all_ok = false;
                $why_failed = "Missing function: " . $functionnameset;
                break;
            }
            if (method_exists($this, $functionnameget) == false) {
                $all_ok = false;
                $why_failed = "Missing function: " . $functionnameget;
                break;
            }
            $value = $copy->$functionnameget();
            $status = $this->$functionnameset($value);
            if (is_array($status) == false) {
                $why_failed = "reply from function " . $functionnameset . " should be an array";
                $all_ok = false;
                break;
            }
            if ($status["status"] == false) {
                $why_failed = $status["message"];
                $all_ok = false;
                break;
            }
        }
        if ($all_ok == false) {
            $this->addError(__FILE__, __FUNCTION__, $why_failed);
            $this->save_dataset = $rollback_savedataset;
            $this->dataset = $rollback_dataset;
        }
        return $all_ok;
    }
}
