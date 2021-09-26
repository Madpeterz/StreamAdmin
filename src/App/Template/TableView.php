<?php

namespace App\Template;

use App\R7\Model\Datatable;

class TableView extends BasicView
{
    public function renderTable(
        array $table_head,
        array $table_body,
        string $classaddon = "",
        bool $show_head = true
    ): string {
        $output = '<table class="' . $classaddon . ' table table-striped">';
        if ($show_head == true) {
            $output .= '<thead><tr>';
            foreach ($table_head as $entry) {
                $output .= '<th scope="col">' . $entry . '</th>';
            }
            $output .= '</tr></thead>';
        }

        $output .= '<tbody>';
        foreach ($table_body as $row) {
            if (is_array($row) == true) {
                $output .= "<tr>";
                foreach ($row as $entry) {
                    $output .= "<td>" . $entry . "</td>";
                }
                $output .= "</tr>";
            }
        }
        $output .= '</tbody>';
        $output .= '</table>';
        return $output;
    }
    public function renderDatatable(array $table_head, array $table_body, ?int $datatableID = null): string
    {
        $this->addVendor("datatable");
        $defaultRender = $this->renderTable($table_head, $table_body, "datatable-default display responsive");
        if ($datatableID === null) {
            return $defaultRender;
        }
        $datatableDriver = new Datatable();
        $datatableDriver->limitFields(["hideColZero","col","dir"]);
        if ($datatableDriver->loadID($datatableID) == false) {
            return $defaultRender;
        }
        $this->output->addSwapTagString("html_js_onready", "
        $('.customdatatable" . $datatableID . "').DataTable({
            'order': [[ " . $datatableDriver->getCol() . ", '" . $datatableDriver->getDir() . "' ]],
            responsive: true,
                  pageLength: " . $this->slconfig->getDatatableItemsPerPage() . ",
                  lengthMenu: [[25, 10, 25, 50, -1], [\"Custom\", 10, 25, 50, \"All\"]],
            language: {
              searchPlaceholder: 'Search...',
              sSearch: '',
              lengthMenu: '_MENU_ items/page',
              }");
        if ($datatableDriver->getHideColZero() == true) {
            $this->output->addSwapTagString(
                "html_js_onready",
                ", 'columnDefs': [
                {
                    'targets': [ 0 ],
                    'visible': false,
                    'searchable': false
                }]"
            );
        }
        $this->output->addSwapTagString("html_js_onready", "});");
        return $this->renderTable($table_head, $table_body, "customdatatable" . $datatableID . " display responsive");
    }
}
