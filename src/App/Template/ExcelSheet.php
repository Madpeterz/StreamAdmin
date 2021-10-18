<?php

namespace App\Template;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

abstract class ExcelSheet extends View
{
    protected string $method = "";
    protected string $action = "";
    protected bool $asAjax = false;
    protected Spreadsheet $spreadsheet;
    protected string $filename = "";
    public function __construct(bool $AutoLoadTemplate = false)
    {
        parent::__construct($AutoLoadTemplate);
        $this->output->tempateAjax();
        $this->spreadsheet = new Spreadsheet();
        $this->spreadsheet->removeSheetByIndex(0);
    }
    public function getoutput(): void
    {
        $this->renderPage();
    }
    public function renderPage(): void
    {
        if ($this->asAjax == true) {
            $this->output->renderAjax();
            return;
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $this->filename . '"');
        header('Cache-Control: max-age=0');
        $writer = IOFactory::createWriter($this->spreadsheet, 'Xlsx');
        $writer->save('php://output');
    }
}
