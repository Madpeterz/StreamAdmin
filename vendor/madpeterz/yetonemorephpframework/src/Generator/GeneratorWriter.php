<?php

namespace YAPF\Framework\Generator;

class GeneratorWriter extends GeneratorDefaults
{
    protected function lines2text(array $lines): string
    {
        $file_content = "";
        $tabs = 0;
        foreach ($lines as $line_data) {
            if (is_array($line_data) == false) {
                if ($file_content != "") {
                    $file_content .= "\n";
                    $file_content .= $this->tab_lookup[$tabs];
                }
                $file_content .= $line_data;
            } else {
                $tabs = $line_data[0];
            }
        }
        $file_content .= "\n";
        return $file_content;
    }
    protected function writeFile(string $contents, string $name, string $folder): void
    {
        $create_file = $folder . $name;
        if ($this->use_output == true) {
            if ($this->console_output == true) {
                echo " - ";
            } else {
                $this->output .=  "<td>";
            }
        }
        $this->writeModelFile($create_file, $contents);
    }
    protected function writeModelFile(string $create_file, string $file_content = ""): void
    {
        if (file_exists($create_file) == true) {
            unlink($create_file);
            usleep((30 * 0.001) * 10000); // wait for 300ms for the disk to finish
        }
        $this->counter_models_failed++;

        $status = file_put_contents($create_file, $file_content);
        usleep((10 * 0.001) * 10000);  // wait for 100ms for the disk to finish

        if ($status !== false) {
            $this->counter_models_failed--;
            $this->counter_models_created++;
            if ($this->use_output == true) {
                if ($this->console_output == true) {
                    echo "\033[32mOk \033[0m";
                } else {
                    $this->output .=  " <font color=\"#00FF00\">Ok</font>";
                }
            }
            return;
        }

        if ($this->use_output == true) {
            if ($this->console_output == true) {
                echo "\033[31mFailed to write \033[0m";
            } else {
                $this->output .=  " <font color=\"#FF0000\">Failed to write</font>";
            }
        }
    }
}
