<?php

namespace YAPF\Framework\Generator;

abstract class ModelFactoryShared
{
    protected $string_types = ["varchar","text","char","longtext","mediumtext","tinytext","date","datetime"];
    protected $int_types = ["tinyint", "int","smallint","bigint","mediumint","enum","timestamp"];
    protected $float_types = ["decimal","float","double"];
    protected $known_types = [];


    protected array $file_lines = [];
    protected string $classname = "";
    protected string $namespaceSingle = "";
    protected string $namespaceSet = "";
    protected string $database = "";
    protected string $table = "";
    protected array $cols = [];
    protected array $links = [];
    protected bool $addDbToTable = false;

    public function __construct(
        string $classname,
        string $namespaceSingle,
        string $namespaceSet,
        string $database,
        string $table,
        array $cols,
        array $relatedLinks,
        bool $addDbToTable
    ) {
        $this->classname = $classname;
        $this->namespaceSingle = $namespaceSingle;
        $this->namespaceSet = $namespaceSet;
        $this->database = $database;
        $this->table = $table;
        $this->cols = $cols;
        $this->links = $relatedLinks;
        $this->addDbToTable = $addDbToTable;
        $this->known_types = array_merge($this->string_types, $this->int_types, $this->float_types);
        $this->createNow();
    }

    public function createNow(): void
    {
        $this->createModelHeader();
        $this->createModelDataset();
        $this->createModelSetters();
        $this->createModelGetters();
        $this->createModelLoaders();
        $this->createRelatedLoaders();
        $this->createModelFooter();
    }

    protected function createModelFooter(): void
    {
    }
    protected function createRelatedLoaders(): void
    {
    }
    protected function createModelGetters(): void
    {
    }
    protected function createModelSetters(): void
    {
    }
    protected function createModelDataset(): void
    {
    }
    protected function createModelHeader(): void
    {
    }
    protected function createModelLoaders(): void
    {
    }

    /**
     * getLines
     * @return array<string>
     */
    public function getLines(): array
    {
        return $this->file_lines;
    }

   /**
     * getColType
     * returns the col type for the selected target_table
     */
    protected function getColType(
        string $target_type,
        string $col_type,
        string $table,
        string $colname
    ): string {
        if (in_array($target_type, $this->known_types) == false) {
            $error_msg = "Table: " . $table . " Column: " . $colname . " unknown type: ";
            $error_msg .= $target_type . " defaulting to string!<br/>";
            if ($this->use_output == true) {
                if ($this->console_output == true) {
                    echo "Error: " . $error_msg . " \n";
                } else {
                    $this->output .=  $error_msg;
                    $this->output .=  "<br/>";
                }
            }
            return "str";
        }
        if (in_array($target_type, $this->int_types)) {
            if (strpos($col_type, 'tinyint(1)') !== false) {
                return "bool";
            }
            return "int";
        }
        if (in_array($target_type, $this->float_types)) {
            return "float";
        }
        return "str";
    }
}
