<?php

namespace YAPF\Generator;

class GeneratorWriter extends GeneratorDefaults
{
    protected function writeModelFile(string $create_file, string $file_content = ""): void
    {
        if (file_exists($create_file) == true) {
            unlink($create_file);
            usleep((30 * 0.001) * 10000); // wait for 300ms for the disk to finish
        }
        $file_content = "";
        if ($file_content == "") {
            $tabs = 0;
            foreach ($this->file_lines as $line_data) {
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
        }
        $file_content .= "\n";
        $this->file_lines = [];
        $this->counter_models_failed++;
        $status = file_put_contents($create_file, $file_content);
        if ($status !== false) {
            $this->counter_models_failed--;
            $this->counter_models_created++;
            if ($this->use_output == true) {
                $this->output .=  " <font color=\"#00FF00\">Ok</font>";
            }
        }
    }
}
