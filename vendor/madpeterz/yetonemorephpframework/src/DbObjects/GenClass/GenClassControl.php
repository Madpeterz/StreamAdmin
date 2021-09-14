<?php

namespace YAPF\DbObjects\GenClass;

use Exception;
use YAPF\Cache\Cache;
use YAPF\Core\SQLi\SqlConnectedClass as SqlConnectedClass;

abstract class GenClassControl extends SqlConnectedClass
{
    protected ?Cache $cache = null;
    protected bool $cacheAllowChanged = false;
    protected $use_table = "";
    protected $save_dataset = [];
    protected $dataset = [];
    protected $allow_set_field = true;
    public $bad_id = false;
    public $use_id_field = "id";

    protected bool $disableUpdates = false;
    protected ?array $limitedFields = null;

    public function limitFields(array $fields): void
    {
        if (in_array($this->use_id_field, $fields) == false) {
            $fields = array_merge([$this->use_id_field], $fields);
        }
        $this->limitedFields = $fields;
        $this->noUpdates();
    }

    public function getUpdatesStatus(): bool
    {
        return $this->disableUpdates;
    }
    public function noUpdates(): void
    {
        $this->disableUpdates = true;
    }

    /**
     * createUID
     * public alias of overloadCreateUID
     * creates a UID based on the target field that does not exist in the datbase
     * @return mixed[] [status => bool, message =>  string, uid => ?string]
     */
    public function createUID(string $onfield, int $length): array
    {
        $feedValues = [time(), microtime(), rand(200, 300)];
        $testuid = substr(md5(implode(".", $feedValues)), 0, $length);
        $where_config = [
            "fields" => [$onfield],
            "values" => [$testuid],
            "types" => ["s"],
            "matches" => ["="],
        ];
        $count_check = $this->sql->basicCountV2($this->getTable(), $where_config);
        $message = "Unable to check DB for UID";
        $status = false;
        $applyed_uid = null;
        if ($count_check["status"] == true) {
            $message = "created uid in use, please try again";
            if ($count_check["count"] == 0) {
                $status = true;
                $message = "ok";
                $applyed_uid = $testuid;
            }
        }
        return [
            "status" => $status,
            "message" => $message,
            "uid" => $applyed_uid,
        ];
    }
    /**
     * fieldsHash
     * creates a sha256 hash imploded by || of the value of all fields
     * that are not in exclude_fields
     */
    public function fieldsHash(array $exclude_fields = ["id"]): string
    {
        $bits = [];
        $fields = $this->getFields();
        foreach ($fields as $fieldname) {
            if (in_array($fieldname, $exclude_fields) == false) {
                $bits[] = $this->getField($fieldname);
            }
        }
        return hash("sha256", implode("||", $bits));
    }
    /**
     * objectToMappedArray
     * returns an key => value array for all fields and their values
     * @return mixed[] [mixed => mixed,...]
     */
    public function objectToMappedArray(array $ignoreFields = []): array
    {
        $reply = [];
        $keys = array_keys($this->dataset);
        foreach ($keys as $fieldname) {
            if (in_array($fieldname, $ignoreFields) == false) {
                $reply[$fieldname] = $this->getField($fieldname);
            }
        }
        return $reply;
    }
    /**
     * objectToValueArray
     * returns an aray of all values for this object
     * knida pointless I might remove this later
     * @return mixed[] [mixed,...]
     */
    public function objectToValueArray(array $ignoreFields = []): array
    {
        return array_values($this->objectToMappedArray($ignoreFields));
    }
    /**
     * hasField
     * checks if the object has the selected field
     */
    public function hasField(string $fieldname): bool
    {
        return array_key_exists($fieldname, $this->dataset);
    }
    /**
     * getFieldType
     * returns the field type as a string
     * or null if not found, null also creates an error
     */
    public function getFieldType(string $fieldname, bool $as_mysqli_code = false): ?string
    {
        if (array_key_exists($fieldname, $this->dataset) == false) {
            $error_meesage = " Attempting to read a fieldtype [" . $fieldname . "] that does not exist";
            $this->addError(__FILE__, __FUNCTION__, get_class($this) . $error_meesage);
            return null;
        }
        if ($as_mysqli_code == true) {
            if ($this->dataset[$fieldname]["type"] == "str") {
                return "s";
            } elseif ($this->dataset[$fieldname]["type"] == "float") {
                return "d";
            }
            return "i";
        }
        return $this->dataset[$fieldname]["type"];
    }
    /**
     * getId
     * returns the ID for the object
     */
    public function getId(): ?int
    {
        return $this->getField($this->use_id_field);
    }
    /**
     * getFields
     * returns an array of all fields for the object
     * @return string[]
     */
    public function getFields(): array
    {
        return array_keys($this->dataset);
    }
    /**
     * isLoaded
     * returns a bool if the object is loaded from DB
     * Notes: Does not support custom Ids
     */
    public function isLoaded(): bool
    {
        if (array_key_exists("id", $this->dataset) == true) {
            if ($this->getField("id") > 0) {
                return true;
            }
        }
        return false;
    }
    /**
     * getField
     * returns the value of a field
     * or null if not supported/not loaded,
     * @return mixed
     */
    protected function getField(string $fieldname)
    {
        if (array_key_exists($fieldname, $this->dataset) == true) {
            $value = $this->dataset[$fieldname]["value"];
            if ($value === null) {
                return null;
            }
            if ($this->dataset[$fieldname]["type"] == "int") {
                $value = intval($value);
            } elseif ($this->dataset[$fieldname]["type"] == "bool") {
                $value = in_array($value, [1,"1","true",true,"yes"], true);
            } elseif ($this->dataset[$fieldname]["type"] == "float") {
                $value = floatval($value);
            }
            return $value;
        }
        $error_message = "Attempting to read a field [" . $fieldname . "]";
        $error_message .= " from a unloaded object, please check the code";
        $this->addError(__FILE__, __FUNCTION__, get_class($this) . " " . $error_message);
        return null;
    }
    /**
     * getTable
     * returns the table assigned to this object
     */
    public function getTable(): string
    {
        return $this->use_table;
    }

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
        if ($this->disableUpdates == true) {
            return $this->addError(__FILE__, __FUNCTION__, "Attempt to update with limitFields enabled!");
        }
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
        if ($this->disableUpdates == true) {
            return $this->addError(__FILE__, __FUNCTION__, "Attempt to update with limitFields enabled!");
        }
        if (count($this->dataset) != count($this->save_dataset)) {
            $this->save_dataset = $this->dataset;
        }
        $check = $this->checkUpdateField($fieldname, $value, $ignore_set_id_warning);
        if ($check["status"] == false) {
            return $check;
        }
        $this->dataset[$fieldname]["value"] = $value;
        if ($this->getFieldType($fieldname) == "bool") {
            $this->dataset[$fieldname]["value"] = 0;
        }
        if (in_array($value, [1, "1", "true", true, "yes"], true) == true) {
            $this->dataset[$fieldname]["value"] = 1;
        }
        return ["status" => true, "message" => "value set"];
    }
    /**
     * checkUpdateField
     * checks if the update field request can be accepted
     * @return mixed[] [status =>  bool, message =>  string] or just [status => bool] if success
     */
    protected function checkUpdateField(string $fieldname, $value, bool $ignore_set_id_warning = false): array
    {
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
        return ["status" => true];
    }

    /*
        bulkChange
        takes in a name => value pairs as an array
        passes that to the set function

        on failure rolls back any changes to the object
    */
    public function bulkChange(array $namevaluepairs): bool
    {
        if ($this->disableUpdates == true) {
            $this->addError(__FILE__, __FUNCTION__, "Attempt to update with limitFields enabled!");
            return false;
        }
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
        if ($this->disableUpdates == true) {
            $this->addError(__FILE__, __FUNCTION__, "Attempt to update with limitFields enabled!");
            return false;
        }
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
