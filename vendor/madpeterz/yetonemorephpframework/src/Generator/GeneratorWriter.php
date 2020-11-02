<?php

namespace YAPF\Generator;

class GeneratorWriter extends GeneratorDefaults
{
    protected function writeModelFile(string $create_file, int $retrys = 0, string $file_content = ""): void
    {
        if (file_exists($create_file)) {
            unlink($create_file);
            usleep((30 * 0.001) * 100); // wait for 3ms for the disk to finish
        }
        $file_content = "";
        if ($file_content == "") {
            $tabs = 0;
            foreach ($this->file_lines as $line_data) {
                if (is_array($line_data) == false) {
                    if ($file_content != "") {
                        $file_content .= "\n";
                    }
                    $file_content .= $tab_lookup[$tabs];
                    $file_content .= $line_data;
                } else {
                    $tabs = $line_data[0];
                }
            }
        }
        $this->file_lines = [];
        $status = file_put_contents($create_file, $file_content);
        if ($status === false) {
            echo " Failed to save file";
            if ($retrys < 3) {
                $retrys++;
                echo " retrying - Attempt: " . ($retrys + 1);
                usleep((30 * 0.001) * 1000); // wait for 30ms and retry
                $this->writeModelFile($create_file, $retrys, $file_content);
            } else {
                echo " Failed<br/>";
            }
        } else {
            echo " OK<br/>";
        }
    }
}
