<?php

namespace YAPF\Framework\Generator;

class ModelFactory extends GeneratorWriter
{
    protected function createFromTable(string $database, string $table, array $fkLink): void
    {
        if ($this->use_output == true) {
            if ($this->console_output == true) {
                echo "Table: " . $table . " ~ ";
            } else {
                $this->output .= "<td>" . $table . "</td>";
            }
        }
        $cols = $this->getTableColumns($database, $table);
        if ($this->use_output == true) {
            if ($this->console_output == true) {
                echo "Single: ";
            }
        }
        $this->createSingle($database, $table, $cols, $fkLink);
        if ($this->use_output == true) {
            if ($this->console_output == true) {
                echo "Set: ";
            }
        }
        $this->createSet($database, $table, $cols, $fkLink);
        if ($this->use_output == true) {
            if ($this->console_output == true) {
                echo "\n";
            }
        }
    }

    protected function createSet(string $database, string $table, array $cols, array $links): void
    {
        global $GEN_NAMESPACE_SET, $GEN_NAMESPACE_SINGLE, $GEN_SAVE_SET_MODELS_TO, $GEN_ADD_DB_TO_TABLE;
        $class_name = ucfirst(strtolower($table));
        $set = new SetModelFactory(
            $class_name,
            $GEN_NAMESPACE_SINGLE,
            $GEN_NAMESPACE_SET,
            $database,
            $table,
            $cols,
            $links,
            $GEN_ADD_DB_TO_TABLE
        );

        $filename = $class_name . "Set.php";

        $this->writeFile($this->lines2text($set->getLines()), $filename, $GEN_SAVE_SET_MODELS_TO);
        $this->counter_models_related_actions += $set->getRelatedCounter();
    }

    protected function createSingle(string $database, string $table, array $cols, array $links): void
    {
        global $GEN_NAMESPACE_SET, $GEN_NAMESPACE_SINGLE, $GEN_SAVE_MODELS_TO, $GEN_ADD_DB_TO_TABLE;
        $class_name = ucfirst(strtolower($table));

        $single = new SingleModelFactory(
            $class_name,
            $GEN_NAMESPACE_SINGLE,
            $GEN_NAMESPACE_SET,
            $database,
            $table,
            $cols,
            $links,
            $GEN_ADD_DB_TO_TABLE
        );

        $filename = $class_name . ".php";

        $this->writeFile($this->lines2text($single->getLines()), $filename, $GEN_SAVE_MODELS_TO);
        $this->counter_models_related_actions += $single->getRelatedCounter();
    }

    /**
     * getTableColumns
     * returns the table schema.columns or null
     * @return mixed[] or null
     */
    protected function getTableColumns(string $target_database, string $target_table): ?array
    {
        $where_config = [
            "fields" => ["TABLE_SCHEMA", "TABLE_NAME"],
            "matches" => ["=","="],
            "values" => [$target_database, $target_table],
            "types" => ["s", "s"],
        ];
        $basic_config = [
            "table" => "information_schema.columns",
            "fields" => ["COLUMN_NAME","COLUMN_DEFAULT","DATA_TYPE","COLUMN_TYPE"],
        ];
        $results = $this->sql->selectV2($basic_config, null, $where_config);
        $returndata = null;
        if ($results["status"] == true) {
            $returndata = $results["dataset"];
        }
        return $returndata;
    }
}
