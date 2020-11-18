<?php

namespace YAPF\DbObjects\GenClass;

use YAPF\Core\SqlConnectedClass as SqlConnectedClass;

abstract class GenClassGet extends SqlConnectedClass
{
    protected $use_table = "";
    protected $save_dataset = [];
    protected $dataset = [];
    protected $allow_set_field = true;
    public $bad_id = false;
    public $use_id_field = "id";
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
     * getHash
     * creates a sha256 hash imploded by || of the value of all fields
     * that are not in exclude_fields
     */
    public function getHash(array $exclude_fields = ["id"]): string
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
    public function objectToMappedArray(): array
    {
        $reply = [];
        $keys = array_keys($this->dataset);
        foreach ($keys as $fieldname) {
            $reply[$fieldname] = $this->getField($fieldname);
        }
        return $reply;
    }
    /**
     * objectToValueArray
     * returns an aray of all values for this object
     * knida pointless I might remove this later
     * @return mixed[] [mixed,...]
     */
    public function objectToValueArray(): array
    {
        return array_values($this->objectToMappedArray());
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
}
