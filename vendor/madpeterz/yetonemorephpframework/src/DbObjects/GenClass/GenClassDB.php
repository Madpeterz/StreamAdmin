<?php

namespace YAPF\DbObjects\GenClass;

abstract class GenClassDB extends GenClassLoad
{
    /**
     * removeMe
     * removes the loaded object from the database
     * and marks the object as unloaded by setting its id to -1
     * @return mixed[] [status =>  bool, message =>  string]
     */
    public function removeEntry(): array
    {
        if ($this->disabled == false) {
            if ($this->getId() > 0) {
                $where_config = [
                    "fields" => ["id"],
                    "values" => [$this->getId()],
                    "types" => ["i"],
                    "matches" => ["="],
                ];
                $remove_status = $this->sql->removeV2($this->getTable(), $where_config);
                if ($remove_status["status"] == true) {
                    $this->dataset["id"]["value"] = -1;
                    if ($this->cache != null) {
                        $this->cache->markChangeToTable($this->getTable());
                    }
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
            if (array_key_exists("id", $this->dataset) == true) {
                if ($this->save_dataset["id"]["value"] == null) {
                    $fields = [];
                    $values = [];
                    $types = [];
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
                            $values[] = $value;
                            $types[] = $update_code;
                        }
                    }
                    $return_dataset = ["status" => false,"message" => "Nothing processed"];
                    if (count($fields) > 0) {
                        $config = [
                            "table" => $this->getTable(),
                            "fields" => $fields,
                            "values" => $values,
                            "types" => $types,
                        ];
                        $return_dataset = $this->sql->addV2($config);
                        if ($return_dataset["status"] == true) {
                            if ($this->cache != null) {
                                $this->cache->markChangeToTable($this->getTable());
                            }
                            $this->dataset["id"]["value"] = $return_dataset["newID"];
                            $this->save_dataset["id"]["value"] = $return_dataset["newID"];
                        }
                    }
                    return $return_dataset;
                }
                $error_msg = "attempting to create a object with a set id, this is not allowed!";
                return ["status" => false, "message" => $error_msg];
            }
            return ["status" => false, "message" => "id field is required on the class to support create"];
        }
        return ["status" => false, "message" => "this class is disabled."];
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
            $error_msg = "Object does not have its id field set!";
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
                $reply = $this->sql->updateV2($this->getTable(), $update_config, $where_config, 1);
                if ($reply["status"] == true) {
                    if ($this->cache != null) {
                        $this->cache->markChangeToTable($this->getTable());
                    }
                }
                return $reply;
            }
            $error_msg = "No changes made";
            return ["status" => false, "changes" => 0, "message" => $error_msg];
        }
        $error_msg = "request rejected: " . $error_msg;
        return ["status" => false, "changes" => 0, "message" => $error_msg];
    }
}
