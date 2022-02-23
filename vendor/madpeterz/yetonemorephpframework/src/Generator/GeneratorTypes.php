<?php

namespace YAPF\Framework\Generator;

use YAPF\Framework\Core\SQLi\SqlConnectedClass as SqlConnectedClass;

abstract class GeneratorTypes extends SqlConnectedClass
{
    public function __construct()
    {
        parent::__construct();
        if (defined('STDIN') == true) {
            $this->console_output = true;
        }
    }
    protected $counter_models_created = 0;
    protected $counter_models_failed = 0;
    protected $counter_models_related_actions = 0;
    protected $use_output = true;
    protected $console_output = false;
    public function noOutput(): void
    {
        $this->use_output = false;
    }
    public function getModelsCreated(): int
    {
        return $this->counter_models_created;
    }
    public function getTotalRelatedActions(): int
    {
        return $this->counter_models_related_actions;
    }
    public function getModelsFailed(): int
    {
        return $this->counter_models_failed;
    }
}
