<?php

namespace YAPF\DbObjects\CollectionSet;

abstract class CollectionSet extends CollectionSetBulkRemove
{
    /**
     * loadIds
     * alias of loadDataFromList
     * preconfiged for ids but overrideable for other fields
     * set ids_clean to false if you are unsure if there are repeated ids
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
     */
    public function loadIds(array $ids, string $field = "id", bool $ids_clean = true): array
    {
        return $this->loadDataFromList($field, $ids, $ids_clean);
    }
    /**
     * loadOnFields
     * uses multiple fields to load from the database with
     * change $word to adjust targeting
     * by default its set to And.
     * for full control please use the method loadWithConfig
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
     */
    public function loadOnFields(
        array $fields,
        array $fieldvalues,
        array $matchtypes,
        string $word = "AND",
        string $order_by = "id",
        string $by_direction = "DESC",
        int $limit = 0
    ): array {
        $this->makeWorker();
        $where_config = [
            "join_with" => $word,
            "fields" => $fields,
            "values" => $fieldvalues,
            "types" => [],
            "matches" => $matchtypes,
        ];
        $unpack_ok = true;
        $unpack_error = "";
        $loop = 0;
        $total = count($fields);
        while ($loop < $total) {
            $fieldname = $fields[$loop];
            if (method_exists($this->worker, "get" . ucfirst($fieldname)) == false) {
                $unpack_ok = false;
                $unpack_error = "get" . ucfirst($fieldname) . " is not supported on worker";
                break;
            }
            $where_config["types"][] = $this->worker->getFieldType($fieldname, true);
            $loop++;
        }
        if ($unpack_ok == true) {
            return $this->loadWithConfig(
                $where_config,
                ["ordering_enabled" => true,"order_field" => $order_by,"order_dir" => $by_direction],
                ["page_number" => 0,"max_entrys" => $limit]
            );
        }
        return $this->addError(__FILE__, __FUNCTION__, $unpack_error, ["count" => 0]);
    }
    /**
     * loadByField
     * alias of loadOnField
     * uses one field to load from the database with
     * for full control please use the method loadWithConfig
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
     */
    public function loadByField(
        string $field,
        $value,
        int $limit = 0,
        string $order = "id",
        string $order_dir = "DESC"
    ): array {
        return $this->loadOnField($field, $value, $limit, $order, $order_dir);
    }
    /**
     * loadOnField
     * uses one field to load from the database with
     * for full control please use the method loadWithConfig
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
     */
    public function loadOnField(
        string $field,
        $value,
        int $limit = 0,
        string $order = "id",
        string $order_dir = "DESC"
    ): array {
        if (is_object($value) == true) {
            $errormsg = "Attempted to pass value as a object!";
            $this->addError(__FILE__, __FUNCTION__, $errormsg);
            return ["status" => false,"message" => "Attempted to pass a value as a object!"];
        }
        return $this->loadOnFields([$field], [$value], ["="], "AND", $order, $order_dir, $limit);
    }
   /**
     * loadLimited
     * paged loading support with limiters
     * for full control please use the method loadWithConfig
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
     */
    public function loadLimited(
        int $limit = 12,
        string $by_field = "id",
        string $by_direction = "ASC",
        array $wherefields = [],
        array $wherevalues = [],
        string $word = "AND",
        int $page = 0
    ): array {
        return $this->loadNewest($limit, $wherefields, $wherevalues, $by_field, $by_direction, $page, $word);
    }
   /**
     * loadNewest
     * alias of loadLimited
     * default setup is to order by id newest first.
     * for full control please use the method loadWithConfig
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
     */
    public function loadNewest(
        int $limit = 12,
        array $wherefields = [],
        array $wherevalues = [],
        string $order_by = "id",
        string $by_direction = "DESC",
        int $page = 0,
        string $joinword = "AND"
    ): array {
        $where_config = [
            "join_with" => $joinword,
             "fields" => array_keys($wherefields),
             "matches" => array_values($wherefields),
             "values" => array_keys($wherevalues),
             "types" => array_values($wherevalues),
        ];
        return $this->loadWithConfig(
            $where_config,
            ["ordering_enabled" => true,"order_field" => $order_by,"order_dir" => $by_direction],
            ["page_number" => $page,"max_entrys" => $limit]
        );
    }
   /**
     * loadAll
     * Loads everything it can get its hands
     * ordered by id ASC by default
     * for full control please use the method loadWithConfig
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
     */
    public function loadAll(string $order_by = "id", string $by_direction = "ASC"): array
    {
        return $this->loadWithConfig(
            null,
            ["ordering_enabled" => true,"order_field" => $order_by,"order_dir" => $by_direction]
        );
    }
   /**
     * loadWithConfig
     * Uses the select V2 system to load data
     * its magic!
     * see the v2 readme
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
     */
    public function loadWithConfig(
        ?array $where_config = null,
        ?array $order_config = null,
        ?array $options_config = null,
        ?array $join_tables = null
    ): array {
        $this->makeWorker();
        $load_data = $this->sql->selectV2(
            ["table" => $this->worker->getTable()],
            $order_config,
            $where_config,
            $options_config,
            $join_tables
        );
        if ($load_data["status"] == false) {
            $error_msg = get_class($this) . " Unable to load data: " . $load_data["message"];
            return $this->addError(__FILE__, __FUNCTION__, $error_msg, ["count" => 0]);
        }
        return $this->processLoad($load_data);
    }
    /**
     * loadDataFromList
     * using the magic of IN we load all objects
     * with the selected field that their value matchs
     * anything in the $values array
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
     */
    protected function loadDataFromList(string $fieldname = "id", array $values = []): array
    {
        $this->makeWorker();
        $uids = [];
        foreach ($values as $id) {
            if (in_array($id, $uids) == false) {
                $uids[] = $id;
            }
        }
        if (count($uids) == 0) {
            return $this->addError(__FILE__, __FUNCTION__, "No ids sent!", ["count" => 0]);
        }
        $typecheck = $this->worker->getFieldType($fieldname, true);
        if ($typecheck == null) {
            return [
                "status" => false,
                "count" => 0,
                "message" => "Invaild field",
            ];
        }
        return $this->loadWithConfig([
            "fields" => [$fieldname],
            "matches" => ["IN"],
            "values" => [$uids],
            "types" => [$typecheck],
        ]);
    }
    /**
     * processLoad
     * takes the reply from mysqli and fills out objects and builds the collection
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
     */
    protected function processLoad($load_data = []): array
    {
        $this->makeWorker();
        $use_field = $this->worker->use_id_field;
        if ($this->worker->bad_id == false) {
            $use_field = "id";
        }
        foreach ($load_data["dataset"] as $entry) {
            $new_object = new $this->worker_class($entry);
            if ($new_object->isLoaded() == true) {
                $this->collected[$entry[$use_field]] = $new_object;
            }
        }
        return ["status" => true, "count" => count($this->collected), "message" => "ok"];
    }
}
