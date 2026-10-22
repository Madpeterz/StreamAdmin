<?php

declare(strict_types=1);

namespace Contract;

use mysqli;
use RuntimeException;

/**
 * Rebuilds the database from the legacy 2.0.1.x schema before every scenario,
 * then applies the baseline data, the scenario seeds and the optional reset hook.
 */
final class Database
{
    private mysqli $link;

    public function __construct(
        private readonly string $host,
        private readonly string $name,
        private readonly string $user,
        private readonly string $pass,
        private readonly string $sqlDir,
        private readonly string $resetHook = "",
    ) {
        mysqli_report(MYSQLI_REPORT_OFF);
        $link = new mysqli($this->host, $this->user, $this->pass);
        if ($link->connect_errno) {
            throw new RuntimeException("DB connect failed: " . $link->connect_error);
        }
        $link->set_charset("utf8mb4");
        $this->link = $link;
    }

    /**
     * @param string[] $seeds seed file names (without .sql) from sql/seeds
     */
    public function rebuild(array $seeds): void
    {
        $this->exec("DROP DATABASE IF EXISTS `" . $this->name . "`;"
            . "CREATE DATABASE `" . $this->name . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
            . "USE `" . $this->name . "`;");
        $this->execFile($this->sqlDir . "/schema/installer.sql");
        $this->execFile($this->sqlDir . "/schema/2.0.1.1.sql");
        $this->execFile($this->sqlDir . "/reset.sql");
        foreach ($seeds as $seed) {
            $this->execFile($this->sqlDir . "/seeds/" . $seed . ".sql");
        }
        if ($this->resetHook !== "") {
            exec($this->resetHook . " 2>&1", $output, $code);
            if ($code !== 0) {
                throw new RuntimeException("RESET_HOOK failed (" . $code . "): " . implode("\n", $output));
            }
        }
    }

    private function execFile(string $path): void
    {
        $sql = file_get_contents($path);
        if ($sql === false) {
            throw new RuntimeException("Unable to read SQL file: " . $path);
        }
        $this->exec($sql, $path);
    }

    private function exec(string $sql, string $source = "inline"): void
    {
        if ($this->link->multi_query($sql) === false) {
            throw new RuntimeException("SQL error in " . $source . ": " . $this->link->error);
        }
        do {
            $result = $this->link->store_result();
            if ($result !== false) {
                $result->free();
            }
            if ($this->link->errno) {
                throw new RuntimeException("SQL error in " . $source . ": " . $this->link->error);
            }
        } while ($this->link->more_results() && $this->link->next_result());
        if ($this->link->errno) {
            throw new RuntimeException("SQL error in " . $source . ": " . $this->link->error);
        }
    }
}
