<?php

namespace YAPF\Bootstrap\Template;

class PagedInfo
{
    public function render($page_data = []): string
    {
        global $rendered_paged_id;
        $reply = '<div id="accordion' . $rendered_paged_id . '">';
        $tab_id = 1;
        $expanded = "true";
        $hidden = "";
        $show = "show";
        foreach ($page_data as $title => $display) {
            $reply .= '<div class="card">';
                $reply .= '<div class="card-header" id="heading' . $tab_id . '">';
                    $reply .= '<h5 class="mb-0">';
                            $reply .= '<button id="tabid' . $tab_id . '" class="btn btn-link ' . $hidden . ''
                            . '" data-toggle="collapse" data-target="#collapse'
                            . $tab_id . '" aria-expanded="' . $expanded . '" aria-controls="collapse' . $tab_id . '">';
                            $reply .= $title;
                            $reply .= '</button>';
                    $reply .= '</h5>';
                $reply .= "</div>";
                $reply .= '<div id="collapse' . $tab_id . '" class="collapse ' . $show . '" aria-labelledby="heading'
                . $tab_id . '" data-parent="#accordion' . $rendered_paged_id . '">';
                    $reply .= '<div class="card-body">';
                        $reply .= $display;
                    $reply .= '</div>';
                $reply .= '</div>';
            $reply .= '</div>';
            $expanded = "false";
            $hidden = "collapsed";
            $show = "";
            $tab_id++;
        }
        $reply .= '</div>';
        $rendered_paged_id++;
        return $reply;
    }
}
