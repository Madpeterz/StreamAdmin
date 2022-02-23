<?php

namespace YAPF\Bootstrap\Template;

class Grid
{
    protected $col_value = 0;
    protected $row_open = false;
    protected $col_open = false;
    protected $output = "";
    public function getOutput(): string
    {
        $this->closeCol();
        $this->closeRow();
        if ($this->output == null) {
            $this->output = "";
        }
        return $this->output;
    }
    public function addBefore(string $content): void
    {
        $this->output = $content . "" . $this->output;
    }
    public function addAfter(string $content): void
    {
        $this->output .= $content;
    }
    public function addContent(string $content, int $size = 0, bool $center = false): void
    {
        if ($size > 0) {
            $this->col($size);
        }
        if ($center == true) {
            $this->output .= '<div class="d-flex justify-content-center">';
        }
        $this->output .= $content;
        if ($center == true) {
            $this->output .= '</div>';
        }
    }
    public function col(int $size): void
    {
        $this->closeCol();
        if (($this->col_value + $size) > 12) {
            $this->closeRow();
        }
        if ($this->row_open == false) {
            $this->row();
        }
        $this->col_value += $size;
        $this->col_open = true;

        $lookup = [
            12 => [12,12,12,12,12],
            11 => [11,11,11,11,11],
            10 => [10,10,10,10,10],
            9 => [9,9,9,9,9],
            8 => [8,8,8,8,8],
            7 => [7,7,7,7,7],
            6 => [6,6,6,6,6],
            5 => [5,5,5,6,6],
            4 => [4,4,6,6,6],
            3 => [3,3,6,6,6],
            2 => [2,2,6,6,6],
            1 => [1,1,6,6,6],
        ];

        $chart = $lookup[$size];

        $sizeChart = [
            "col-" . $chart[4],
            "col-sm-" . $chart[3],
            "col-md-" . $chart[2],
            "col-lg-" . $chart[1],
            "col-xl-" . $chart[0],
        ];

        $this->output .= '<div class="grid-margin ' . implode(", ", $sizeChart) . '">';
    }
    public function closeRow(): void
    {
        $this->closeCol();
        if ($this->row_open == true) {
            $this->row_open = false;
            $this->col_value = 0;
            $this->output .= '</div>';
        }
    }
    protected function closeCol(): void
    {
        if ($this->col_open == true) {
            $this->col_open = false;
            $this->output .= '</div>';
        }
    }
    protected function row(): void
    {
        $this->closeRow();
        $this->output .= '<div class="row">';
        $this->row_open = true;
    }
}
