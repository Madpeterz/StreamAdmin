<?php

namespace App\Endpoint\View\Home;

use App\Template\Grid;

abstract class HomeDisplayData extends HomeLoadData
{
    protected Grid $sub_grid_streams;
    protected Grid $sub_grid_clients;
    protected Grid $sub_grid_servers;
    protected Grid $sub_grid_objects;
    protected function displayDatasets(): void
    {
        $this->displayStreams();
        $this->displayClients();
        $this->displayServers();
        $this->displayObjects();
        $this->displayVersions();
        $this->renderDisplay();
        $this->renderOwnerDisplay();
    }

    protected function renderOwnerDisplay(): void
    {
        if ($this->session->getOwnerLevel() == 1) {
            if ($this->server_set->getCount() == 0) {
                $this->main_grid->addContent("<hr/>", 12);
                $this->main_grid->addContent(
                    "<a href=\"[[url_base]]import\">"
                    . "<button class=\"btn btn-info btn-block\" type=\"button\">Import from R4</button></a>",
                    12
                );
            }
        }
    }

    protected function renderDisplay(): void
    {
        $this->main_grid->addContent("<br/>", 12);
        $this->main_grid->closeRow();
        $this->main_grid->addContent($this->sub_grid_streams->getOutput(), 6);
        $this->main_grid->addContent($this->sub_grid_clients->getOutput(), 6);
        $this->main_grid->addContent("<br/>", 12);
        $this->main_grid->closeRow();
        $this->main_grid->addContent($this->sub_grid_servers->getOutput(), 6);
        $this->main_grid->addContent($this->sub_grid_objects->getOutput(), 6);
        $this->main_grid->closeRow();
    }

    protected function displayVersions(): void
    {
        if (file_exists("" . ROOTFOLDER . "/App/Versions/sql/" . $this->slconfig->getDbVersion() . ".sql") == true) {
            $this->main_grid->addContent("<div class=\"alert alert-warning\" role=\"alert\">DB update required "
            . "<br/> please run \"App/Versions/sql/" . $this->slconfig->getDbVersion() . ".sql\"</div>", 12);
        }
        if (file_exists("" . ROOTFOLDER . "/App/Versions/about/" . $this->slconfig->getDbVersion() . ".txt") == true) {
            $this->main_grid->closeRow();
            $this->main_grid->addContent("<br/>Version: " . $this->slconfig->getDbVersion() . "", 12);
            $this->main_grid->addContent(
                file_get_contents("" . ROOTFOLDER . "/App/Versions/about/" . $this->slconfig->getDbVersion() . ".txt"),
                12
            );
        } else {
            $this->main_grid->addContent("Version: " . $this->slconfig->getDbVersion() . "", 12);
        }
    }

    protected function displayObjects(): void
    {
        $seen_objects = [];
        $table_head = ["Object type","Last connected","Region"];
        $table_body = [];
        $issues = 0;
        foreach ($this->objects_set->getAllIds() as $object_id) {
            $object = $this->objects_set->getObjectByID($object_id);
            $region = $this->region_set->getObjectByID($object->getRegionLink());
            $entry = [];
            $color = "text-light";
            if (in_array($object->getObjectMode(), $seen_objects) == true) {
                $color = "text-danger";
                $issues++;
            } else {
                $seen_objects[] = $object->getObjectMode();
            }
            $entry[] = '<span class="' . $color . '">'
            . str_replace("server", "", $object->getObjectMode()) . '</span>';
            $color = "text-success";
            $dif = time() - $object->getLastSeen();
            if ($dif > 240) {
                $issues += 5;
                $color = "text-danger";
            } elseif ($dif > 65) {
                $issues++;
                $color = "text-warning";
            }
            $entry[] = '<span class="' . $color . '">' . expiredAgo($object->getLastSeen(), true) . '</span>';
            $tp_url = "http://maps.secondlife.com/secondlife/" . $region->getName() . "/"
             . implode("/", explode(",", $object->getObjectXYZ())) . "";
            $tp_url = str_replace(' ', '%20', $tp_url);
            $entry[] = "<a href=\"" . $tp_url . "\" target=\"_blank\"><i class=\"fas fa-map-marked-alt\"></i> "
            . $region->getName() . "</a>";
            $table_body[] = $entry;
        }
        foreach ($this->owner_objects_list as $objecttype) {
            if (in_array($objecttype, $seen_objects) == false) {
                $issues += 5;
                $entry = [];
                $entry[] = $objecttype;
                $entry[] = "<span class=\"text-warning\">Not connected in the last hour!</span>";
                $entry[] = "/";
                $table_body[] = $entry;
            }
        }
        $this->sub_grid_objects = new Grid();
        $issues_badge = "";
        if ($issues == 0) {
            $issues_badge = '<span class="badge badge-success"><i class="fas fa-check-square"></i></span>';
        } elseif ($issues > 3) {
            $issues_badge = '<span class="badge badge-danger"><i class="fas fa-burn"></i></span>';
        } else {
            $issues_badge = '<span class="badge badge-warning"><i class="far fa-caret-square-right"></i></span>';
        }
        $this->sub_grid_objects->addContent('<h4>SL health ' . $issues_badge . '</h4>', 12);
        $this->sub_grid_objects->addContent($this->renderTable($table_head, $table_body, "", false), 12);
    }

    protected function displayServers(): void
    {
        $table_head = ["Server","Status"];
        $table_body = [];
        foreach ($this->server_set->getAllIds() as $server_id) {
            $server = $this->server_set->getObjectByID($server_id);
            $entry = [];
            $servername = '<a href="[[url_base]]stream/onserver/' . $server->getId() . '"><h5>'
            . $server->getDomain() . '</h5></a>';
            $servername .= '<h6><span class="badge badge-success">Ready <span class="badge badge-light">'
            . $this->server_loads[$server->getId()]["ready"] . '</span></span> ';
            $servername .= '<span class="badge badge-warning">NeedWork <span class="badge badge-light">'
            . $this->server_loads[$server->getId()]["needWork"] . '</span></span> ';
            $servername .= '<span class="badge badge-info">Sold <span class="badge badge-light">'
            . $this->server_loads[$server->getId()]["sold"] . '</span></span></h6>';
            $entry[] = $servername;
            $serverstatus = '<div class="serverstatusdisplay">';
            if ($server->getApiServerStatus() == true) {
                $serverstatus .= '<div data-loading="<div class=\'spinner-border spinner-border-sm '
                . 'text-primary\' role=\'status\'>'
                . '<span class=\'sr-only\'>Loading...</span></div>" data-repeatingrate="7000" class="ajaxonpageload" '
                . 'data-loadmethod="post" data-loadurl="[[url_base]]server/Serverload/'
                . $server->getId() . '"></div>';
            } else {
                $serverstatus .= '<sub> </sub>';
            }
            $serverstatus .= '</div>';
            $entry[] = $serverstatus;
            $table_body[] = $entry;
        }
        $this->sub_grid_servers = new Grid();
        $this->sub_grid_servers->addContent('<h4>servers</h4>', 12);
        $this->sub_grid_servers->addContent($this->renderTable($table_head, $table_body, "", false), 12);
    }

    protected function displayClients(): void
    {
        $this->sub_grid_clients = new Grid();
        $this->sub_grid_clients->addContent('<strong>Clients</strong>', 12);
        $this->sub_grid_clients->addContent(
            '<h5><a href="[[url_base]]client/expired"><span class="badge badge-danger">Expired '
            . '<span class="badge badge-light">' . $this->client_expired . '</span></span></a></h5>',
            4
        );
        $this->sub_grid_clients->addContent(
            '<h5><a href="[[url_base]]client/soon"><span class="badge badge-warning">Expires in 24 '
            . 'hours <span class="badge badge-light">' . $this->client_expires_soon . '</span></span></a></h5>',
            4
        );
        $this->sub_grid_clients->addContent(
            '<h5><a href="[[url_base]]client/active"><span class="badge badge-success">Ok '
            . '<span class="badge badge-light">' . $this->client_ok . '</span></span></a></h5><br/>',
            4
        );
    }

    protected function displayStreams(): void
    {
        $this->sub_grid_streams = new Grid();
        $this->sub_grid_streams->addContent(
            '<strong>Streams</strong>',
            12
        );
        $this->sub_grid_streams->addContent(
            '<h5><a href="[[url_base]]stream/ready"><span class="badge badge-success">'
            . 'Ready <span class="badge badge-light">' . $this->stream_total_ready . '</span></span></a></h5>',
            4
        );
        $this->sub_grid_streams->addContent(
            '<h5><a href="[[url_base]]stream/needWork"><span class="badge badge-warning">'
            . 'NeedWork <span class="badge badge-light">' . $this->stream_total_needWork . '</span></span></a></h5>',
            4
        );
        $this->sub_grid_streams->addContent(
            '<h5><a href="[[url_base]]stream/sold"><span class="badge badge-info">'
            . 'Sold <span class="badge badge-light">' . $this->stream_total_sold . '</span></span></a></h5><br/>',
            4
        );
    }
}
