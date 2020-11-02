<?php

namespace YAPF\DbObjects\CollectionSet;

abstract class CollectionSetLoad extends CollectionSetBulkRemove
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
        string $order = "id",
        string $order_dir = "DESC",
        int $limit = 0
    ): array {
        $this->makeWorker();
        $wherefields = [];
        $wherevalues = [];
        $unpack_ok = true;
        $unpack_error = "";
        $loop = 0;
        $total = count($fields);
        while ($loop < $total) {
            $fieldname = $fields[$loop];
            if (method_exists($this->worker, "get_" . $fieldname) == false) {
                $unpack_ok = false;
                $unpack_error = "get_" . $fieldname . " is not supported on worker";
                break;
            }
            $field_type = $this->worker->getFieldType($fieldname, true);
            if ($field_type == null) {
                $unpack_ok = false;
                $unpack_error = "get_" . $fieldname . " fieldtype is not supported!";
                break;
            }
            $wherefields[] = [$fieldname => $matchtypes[$loop]];
            $wherevalues[] = [$fieldvalues[$loop] => $field_type];
            $loop++;
        }
        if ($unpack_ok == true) {
            return $this->loadData($wherefields, $wherevalues, $word);
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
        return $this->loadOnFields([$field], [$value], ["="], "AND", $order_by, $order_dir, $limit);
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
        return $this->loadData($wherefields, $wherevalues, $word, $by_field, $by_direction, $limit, $page);
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
        string $by_field = "id",
        string $by_direction = "DESC",
        int $page = 0,
        string $word = "AND"
    ): array {
        return $this->loadData($wherefields, $wherevalues, $word, $by_field, $by_direction, $limit, $page);
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
        return $this->loadData([], [], "AND", $order_by, $by_direction, 0, 0);
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
     * loadData
     * takes the other load requests repacks it into V2 and passes it over
     * to loadWithConfig
     * @return mixed[] [status =>  bool, count => integer, message =>  string]
     */
    protected function loadData(
        array $wherefields,
        array $wherevalues,
        string $joinword = "AND",
        string $orderBy = "",
        string $orderDir = "DESC",
        int $limit = 0,
        int $page = 0
    ): array {
        $whereconfig = [
            "join_with" => $joinword,
             "fields" => array_keys($wherefields),
             "matches" => array_values($wherefields),
             "values" => array_keys($wherevalues),
             "type" => array_values($wherevalues),
        ];
        $options_config = [
            "page_number" => $limit,
            "max_entrys" => $page,
        ];
        return $this->loadWithConfig($whereconfig, order, $options_config);
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
        $uids = [];
        foreach ($values as $id) {
            if (in_array($id, $uids) == false) {
                $uids[] = $id;
            }
        }
        if (count($uids) == 0) {
            return $this->addError(__FILE__, __FUNCTION__, "No ids sent!", ["count" => 0]);
        }
        return $this->load_with_config([
            "fields" => [$fieldname],
            "matches" => ["IN"],
            "values" => [$uids],
            "types" => [$new_object->getFieldType($fieldname, true)],
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
        $use_field = "id";
        if ($this->worker->bad_id == true) {
            $use_field = $this->worker->use_id_field;
        }
        foreach ($load_data["dataSet"] as $entry) {
            $new_object = new $this->worker_class();
            if ($new_object->setup($entry) == true) {
                $id_check_passed = true;
                if (require_id_on_load == true) {
                    if ($new_object->getID() <= 0) {
                        $id_check_passed = false;
                    }
                }
                if ($id_check_passed == false) {
                    $error_msg = "Failed id check";
                    return $this->addError(__FILE__, __FUNCTION__, $error_msg, ["count" => count($this->collected)]);
                }
                if ($use_field != null) {
                    $this->collected[$entry[$use_field]] = $new_object;
                } else {
                    $this->collected[count($this->collected)] = $new_object;
                }
            }
        }
        return ["status" => true, "count" => count($this->collected), "message" => "ok"];
    }
}
