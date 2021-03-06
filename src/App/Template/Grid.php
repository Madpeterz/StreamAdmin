<?php

namespace App\Template;

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
    public function addContent(string $content, int $size = 0): void
    {
        if ($size > 0) {
            $this->col($size);
        }
        $this->output .= $content;
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
        $this->output .= '<div class="col-sm-' . $size . ' col-md-' . $size . ' col-lg-' . $size . '">@NL@';
    }
    public function closeRow(): void
    {
        $this->closeCol();
        if ($this->row_open == true) {
            $this->row_open = false;
            $this->col_value = 0;
            $this->output .= '</div>@NL@';
        }
    }
    protected function closeCol(): void
    {
        if ($this->col_open == true) {
            $this->col_open = false;
            $this->output .= '</div>@NL@';
        }
    }
    protected function row(): void
    {
        $this->closeRow();
        $this->output .= '<div class="row">@NL@';
        $this->row_open = true;
    }
}
