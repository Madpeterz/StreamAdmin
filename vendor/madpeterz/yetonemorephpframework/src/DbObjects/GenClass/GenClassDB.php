<?php

namespace YAPF\DbObjects\GenClass;

abstract class GenClassDB extends GenClassLoad
{
    /**
     * removeMe [E_USER_DEPRECATED]
     * Please use removeEntry
     * removes the loaded object from the database
     * and marks the object as unloaded by setting its id to -1
     * @return mixed[] [status =>  bool, message =>  string]
     */
    public function removeMe(): array
    {
        trigger_error("removeMe is being phased out please use removeEntry", E_USER_DEPRECATED);
        return $this->removeEntry();
    }
    /**
     * removeMe
     * removes the loaded object from the database
     * and marks the object as unloaded by setting its id to -1
     * @return mixed[] [status =>  bool, message =>  string]
     */
    public function removeEntry(): array
    {
        if ($this->disabled == false) {
            if ($this->get_id() > 0) {
                $wherefields = [["id" => "="]];
                $wherevalues = [[$this->get_id() => "i"]];
                $remove_status = $this->sql->remove($this->get_table(), $wherefields, $wherevalues);
                if ($remove_status["status"] == true) {
                    $this->dataset["id"]["value"] = -1;
                }
                return $remove_status;
            }
            return ["status" => false, "message" => "this object is not loaded!"];
        }
        return ["status" => false, "message" => "this class is disabled."];
    }
    /**
     * createEntry
     * create a new entry in the database for this object
     * once created it also sets the objects id field
     * @return mixed[] [status =>  bool, message =>  string]
     */
    public function createEntry(): array
    {
        if ($this->disabled == false) {
            if (array_key_exists("id", $this->save_dataset) == true) {
                if ($this->save_dataset["id"]["value"] == null) {
                    $fields = [];
                    $setto = [];
                    foreach ($this->dataset as $key => $value) {
                        if ($key != "id") {
                            $value = $this->dataset[$key]["value"];
                            $fields[] = $key;
                            $update_code = "i";
                            if ($this->dataset[$key]["type"] == "str") {
                                $update_code = "s";
                            } elseif ($this->dataset[$key]["type"] == "float") {
                                $update_code = "d";
                            }
                            $setto[] = [$value => $update_code];
                        }
                    }
                    if (count($fields) > 0) {
                        $add_status = $this->sql->add($this->get_table(), $fields, $setto);
                        if ($add_status["status"] == true) {
                            $this->dataset["id"]["value"] = $add_status["newID"];
                            $this->save_dataset["id"]["value"] = $add_status["newID"];
                        }
                        return $add_status;
                    }
                    return ["status" => false, "message" => "No fields set to create with!"];
                }
                $error_msg = "attempting to create a object with a set id, this is not allowed!";
                return ["status" => false, "message" => $error_msg];
            }
            return ["status" => false, "message" => "All objects must have a id field!"];
        }
        return ["status" => false, "message" => "this class is disabled."];
    }
    /**
     * saveChanges [E_USER_DEPRECATED]
     * updates changes to the object in the database
     * @return mixed[] [status =>  bool, message =>  string]
     */
    public function saveChanges(): array
    {
        trigger_error("saveChanges is being phased out please use updateEntry", E_USER_DEPRECATED);
        return $this->updateEntry();
    }
    /**
     * updateEntry
     * updates changes to the object in the database
     * @return mixed[] [status =>  bool, message =>  string]
     */
    public function updateEntry(): array
    {
        if ($this->disabled == true) {
            $error_msg = "this class is disabled.";
            return ["status" => false, "changes" => 0, "message" => $error_msg];
        }
        if (array_key_exists("id", $this->save_dataset) == false) {
            $error_msg = "Object does not have its if field!";
            return ["status" => false, "changes" => 0, "message" => $error_msg];
        }
        if ($this->save_dataset["id"]["value"] < 1) {
            $error_msg = "Object id is not vaild for updates";
            return ["status" => false, "changes" => 0, "message" => $error_msg];
        }

        $where_config = [
            "fields" => ["id"],
            "matches" => ["="],
            "values" => [$this->save_dataset["id"]["value"]],
            "types" => ["i"],
        ];
        $update_config = [
            "fields" => [],
            "values" => [],
            "types" => [],
        ];
        $had_error = false;
        $error_msg = "";

        foreach ($this->save_dataset as $key => $value) {
            if ($key != "id") {
                if (array_key_exists($key, $this->dataset) == false) {
                    $had_error = true;
                    $error_msg = "Key: " . $key . " is missing from dataset!";
                    break;
                }
                if (array_key_exists("value", $this->dataset[$key]) == false) {
                    $had_error = true;
                    $error_msg = "Key: " . $key . " is missing its value index!";
                    break;
                }
                if ($this->dataset[$key]["value"] != $this->save_dataset[$key]["value"]) {
                    $update_code = "i";
                    if ($this->dataset[$key]["type"] == "str") {
                        $update_code = "s";
                    } elseif ($this->dataset[$key]["type"] == "float") {
                        $update_code = "d";
                    }
                    $update_config["fields"][] = $key;
                    $update_config["values"][] = $this->dataset[$key]["value"];
                    $update_config["types"][] = $update_code;
                }
            }
        }
        if ($had_error == false) {
            $expected_changes = count($update_config["fields"]);
            if ($expected_changes > 0) {
                return $this->sql->updateV2($this->get_table(), $update_config, $where_config, 1);
            }
            $error_msg = "No changes made";
            return ["status" => false, "changes" => 0, "message" => $error_msg];
        }
        $error_msg = "request rejected: " . $error_msg;
        return ["status" => false, "changes" => 0, "message" => $error_msg];
    }
}
