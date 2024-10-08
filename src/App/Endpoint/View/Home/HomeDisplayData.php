<?php

namespace App\Endpoint\View\Home;

use YAPF\Bootstrap\Template\Grid;

abstract class HomeDisplayData extends HomeLoadData
{
    protected Grid $sub_grid_streams;
    protected Grid $sub_grid_clients;
    protected Grid $sub_grid_servers;
    protected Grid $sub_grid_objects;
    protected Grid $sub_grid_Health;
    protected function displayDatasets(): void
    {
        $this->displayStreams();
        $this->displayClients();
        $this->displayServers();
        $this->displayObjects();
        $this->displayVersions();
        $this->renderDisplay();
    }

    protected function renderDisplay(): void
    {
        $this->main_grid->addContent("<br/>", 12);
        $this->main_grid->closeRow();
        $this->main_grid->addContent($this->sub_grid_streams->getOutput(), 6);
        $this->main_grid->addContent($this->sub_grid_clients->getOutput(), 6);
        $this->main_grid->closeRow();
        $this->main_grid->addContent("<br/>", 12);
        $this->main_grid->closeRow();
        $this->main_grid->addContent($this->sub_grid_servers->getOutput(), 6);
        $this->main_grid->addContent($this->sub_grid_objects->getOutput(), 6);
        $this->main_grid->closeRow();
    }

    protected function displayVersions(): void
    {
        if (
            file_exists("../../Versions/" .
                $this->siteConfig->getSlConfig()->getDbVersion() . ".sql") == true
        ) {
            $this->main_grid->addContent("<div class=\"alert alert-warning\" role=\"alert\">DB update required "
                . "<br/> please run \"Versions/" . $this->siteConfig->getSlConfig()->getDbVersion() . ".sql\"</div>", 12);
        }

        $infofile = "../../Versions/about/" .
            $this->siteConfig->getSlConfig()->getDbVersion() . ".txt";
        if (file_exists($infofile) == true) {
            $this->main_grid->closeRow();
            $this->main_grid->addContent("<br/>Version: " . $this->siteConfig->getSlConfig()->getDbVersion() . "", 12);
            $this->main_grid->addContent(
                file_get_contents($infofile),
                12
            );
            return;
        }
        $this->main_grid->addContent("Version: " . $this->siteConfig->getSlConfig()->getDbVersion() . "<br/>
        Unable to read version info file: " . $infofile . "
        ", 12);
    }

    protected function displayObjects(): void
    {
        $seen_objects = [];
        $table_head = ["Object type", "Last connected", "Region"];
        $table_body = [];
        $issues = 0;

        $total = $this->venderHealthGood + $this->venderHealthBad;
        $pcent = 0;
        if ($total > 0) {
            $pcent = ($this->venderHealthGood / $total) * 100;
        }

        $statusreport = "success";
        if ($pcent < 75) {
            $statusreport = "warning";
            $issues += 1;
        }
        if ($pcent < 50) {
            $statusreport = "danger";
            $issues += 5;
        }
        $entry = [];
        $entry[] = "<a href=\"[[SITE_URL]]health\">Vender status</a>";
        $entry[] = "<span class=\"text-" . $statusreport . "\">
        <i class=\"fas fa-heartbeat\"></i> " . round($pcent, 2) . "%</span>";
        $entry[] = "";
        $table_body[] = $entry;

        foreach ($this->objects_set as $object) {
            $region = $this->region_set->getObjectByID($object->getRegionLink());
            $entry = [];
            $color = "text-light";
            $dif = time() - $object->getLastSeen();
            $hide_output = false;
            if (in_array($object->getObjectMode(), $seen_objects) == true) {
                if ($dif > (30 * 60)) {
                    $hide_output = true;
                } else {
                    $color = "text-danger";
                    $issues++;
                }
            } else {
                $seen_objects[] = $object->getObjectMode();
            }
            if ($hide_output == false) {
                $name = $object->getObjectMode();
                $name = strtolower($name);
                $name = str_replace("server", " ", $name);
                $name = ucfirst($name);
                $entry[] = '<span class="' . $color . '">'
                    . $name . '</span>';
                $color = "text-success";

                if ($dif > 240) {
                    $issues += 5;
                    $color = "text-danger";
                } elseif ($dif > 65) {
                    $issues++;
                    $color = "text-warning";
                }
                $regionName = "id " . $object->getRegionLink() . "";
                if ($region != null) {
                    $regionName = $region->getName();
                }

                $entry[] = '<span class="' . $color . '">'
                    . $this->expiredAgo($object->getLastSeen(), true, "Just now") . '</span>';
                $tp_url = "http://maps.secondlife.com/secondlife/" . $regionName . "/"
                    . implode("/", explode(",", $object->getObjectXYZ())) . "";
                $tp_url = str_replace(' ', '%20', $tp_url);
                $regionLinkURL = "<a href=\"" . $tp_url . "\" target=\"_blank\">" .
                    "<i class=\"fas fa-map-marked-alt\"></i> " . $regionName . "</a>";
                if ($regionName == "cron") {
                    $regionLinkURL = "<i class=\"fas fa-history\"></i> Cron";
                }
                $entry[] = $regionLinkURL;
                $table_body[] = $entry;
            }
        }
        foreach ($this->owner_objects_list as $objecttype) {
            if (in_array($objecttype, $seen_objects) == false) {
                $issues += 5;
                $entry = [];
                $objecttype = strtolower($objecttype);
                $objecttype = str_replace("server", " ", $objecttype);
                $objecttype = ucfirst($objecttype);
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
        $this->sub_grid_objects->addContent('<h4>System health ' . $issues_badge . '</h4>', 12);
        $this->sub_grid_objects->addContent($this->renderTable($table_head, $table_body, "", false), 12);
    }

    protected function displayServers(): void
    {
        $table_head = ["Server", "Status"];
        $table_body = [];
        foreach ($this->server_set as $server) {
            $entry = [];
            $servername = '<a href="[[SITE_URL]]stream/onserver/' . $server->getId() . '"><h5>'
                . $server->getDomain() . '</h5></a>';
            $servername .= '<h6><span class="badge badge-success">Ready <span class="badge badge-light">'
                . $this->server_loads[$server->getId()]["ready"] . '</span></span> ';
            $servername .= '<span class="badge badge-warning">NeedWork <span class="badge badge-light">'
                . $this->server_loads[$server->getId()]["needWork"] . '</span></span> ';
            $servername .= '<span class="badge badge-info">Sold <span class="badge badge-light">'
                . $this->server_loads[$server->getId()]["sold"] . '</span></span></h6>';
            $entry[] = $servername;
            $serverstatus = '<div class="serverstatusdisplay">';
            $serverstatus .= '<sub> </sub>';
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
            '<h5><a href="[[SITE_URL]]client/expired"><span class="badge badge-danger">Expired '
                . '<span class="badge badge-light">' . $this->client_expired . '</span></span></a></h5>',
            4
        );
        $this->sub_grid_clients->addContent(
            '<h5><a href="[[SITE_URL]]client/soon"><span class="badge badge-warning">Expires in 24 '
                . 'hours <span class="badge badge-light">' . $this->client_expires_soon . '</span></span></a></h5>',
            4
        );
        $this->sub_grid_clients->addContent(
            '<h5><a href="[[SITE_URL]]client/active"><span class="badge badge-success">Ok '
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
            '<h5><a href="[[SITE_URL]]stream/ready"><span class="badge badge-success">'
                . 'Ready <span class="badge badge-light">' . $this->stream_total_ready . '</span></span></a></h5>',
            4
        );
        $this->sub_grid_streams->addContent(
            '<h5><a href="[[SITE_URL]]stream/Needwork"><span class="badge badge-warning">'
                . 'NeedWork <span class="badge badge-light">' . $this->stream_total_needWork . '</span></span></a></h5>',
            4
        );
        $this->sub_grid_streams->addContent(
            '<h5><a href="[[SITE_URL]]stream/sold"><span class="badge badge-info">'
                . 'Sold <span class="badge badge-light">' . $this->stream_total_sold . '</span></span></a></h5><br/>',
            4
        );
    }
}
