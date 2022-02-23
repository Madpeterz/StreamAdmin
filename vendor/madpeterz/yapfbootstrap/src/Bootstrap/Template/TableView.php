<?php

namespace YAPF\Bootstrap\Template;

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
}
