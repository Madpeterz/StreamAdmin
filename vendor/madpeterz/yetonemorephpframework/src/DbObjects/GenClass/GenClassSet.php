<?php

namespace YAPF\DbObjects\GenClass;

abstract class GenClassSet extends GenClassGet
{
    /**
     * __construct
     * [Optional] takes a key => value array
     * where the key is the field
     * and sets on the object with these values,
     * you should avoid this and use the load[X] methods!
     */
    public function __construct(array $defaults = [])
    {
        if (count($defaults) > 0) {
            $this->setup($defaults);
        }
        parent::__construct();
    }
    /**
     * setup
     * Fills in the dataset with a key => value array
     * used when first loading a object
     * returns true if there was no errors
     */
    protected function setup(array $keyvalues): bool
    {
        $hasErrors = false;
        $saveDataset = $this->dataset;
        foreach ($keyvalues as $key => $value) {
            if (array_key_exists($key, $this->dataset) == true) {
                $this->dataset[$key]["value"] = $value;
            } else {
                $hasErrors = true;
                break;
            }
        }
        if ($hasErrors == true) {
            $this->dataset = $saveDataset;
            return false;
        }
        $this->save_dataset = $this->dataset;
        return true;
    }
    /**
     * setFields
     * public alias of setup
     */
    public function setFields(array $KeyValuePairs): bool
    {
        return $this->setup($KeyValuePairs);
    }
    /**
     * setID
     * creates a UID based on the target field that does not exist in the datbase
     * Does not support objects marked with bad_id
     * Note: Setting the ID can lead to weird side effects!
     * @return mixed[] [status =>  bool, message =>  string]
     */
    public function setID(int $newvalue): array
    {
        if ($this->bad_id == false) {
            return $this->updateField("id", $newvalue, true);
        }
        return ["status" => false , "message" => "bad ID marked"];
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
     * setField [E_USER_DEPRECATED]
     * Please use set_<fieldname>
     * Note: Setting the ID can lead to weird side effects!
     * @return mixed[] [status =>  bool, message =>  string]
     */
    public function setField(string $fieldname, $value, bool $ignore_set_id_warning = false): array
    {
        trigger_error("setField is being phased out please use set_[fieldname]", E_USER_DEPRECATED);
        return $this->updateField($fieldname, $value, $ignore_set_id_warning);
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

        if (($fieldname == "id") && ($ignore_set_id_warning == true)) {
            $this->save_dataset["id"]["value"] = $value;
        } elseif (($fieldname == "id") && ($ignore_set_id_warning == false)) {
            return ["status" => false, "message" => "Setting ID is blocked for this object"];
        }
        return ["status" => true, "message" => "value set"];
    }
}
