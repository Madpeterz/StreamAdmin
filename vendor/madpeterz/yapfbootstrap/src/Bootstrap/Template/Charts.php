<?php

namespace YAPF\Bootstrap\Template;

class Charts
{
    protected string $html_js_onready = "";
    public function getJS(): string
    {
        return $this->html_js_onready;
    }

    protected array $chartNames = [];
    protected array $chartDataSets = [];

    /**
     * @return string[]
     */
    public function getChartNames(): array
    {
        return $this->chartNames;
    }

    public function getChartDatasets(): string
    {
        return json_encode($this->chartDataSets);
    }

    protected bool $secondYaxis = false;
    public function enableSecondYAxis(bool $status = true): void
    {
        $this->secondYaxis = $status;
    }

    /*
        Data setup
        line charts
            [
                "title" => string
                "data" => array
                "borderWidth" => int   (Optional: if not included will auto set to 1)
                "borderColor" => array[] [r,g,b] (Optional: if not included will auto create)
            ]

        for pie charts add

        "title" => string
        "data" => array
        "backgroundColors" => array[]  [r,g,b]  (Optional: if not included will auto create)
        "hoverOffset" => int  (Optional: if not included will auto set to 4)
    */
    protected int $posIndexColor = 0;


    /**
     * Creates a dataset entry line for the chart
     * @param array dataset The dataset to be added to the chart.
     */
    protected function createDatasetEntryLine(array $dataset): void
    {
        if (array_key_exists("yAxisID", $dataset) == false) {
            $dataset["yAxisID"] = "y";
        }
        if (array_key_exists("hoverOffset", $dataset) == false) {
            $dataset["borderWidth"] = 1;
        }
        if (array_key_exists("borderColor", $dataset) == false) {
            $dataset["borderColor"] = $this->createRGBColor($this->posIndexColor);
            $this->posIndexColor++;
        }

        $this->jsonPacked["data"]["datasets"][] = [
            'label' => $dataset["title"],
            'data' => $dataset["data"],
            'borderWidth' => $dataset["borderWidth"],
            'borderColor' => "rgb(" . implode(", ", $dataset["borderColor"]) . ")",
            'backgroundColor' => "rgba(" . implode(", ", $this->darkenColor($dataset["borderColor"])) . ",0.35)",
            'tension' => 0.1,
            'fill' => true,
            'yAxisID' => $dataset["yAxisID"],
        ];
    }

    /**
     * Convert an RGB color to HSL, lower the L by 50% and S by 15%, then convert back to RGB
     * @param array rgb an array of RGB values, e.g. [255, 255, 255]
     * @return int[] An array of RGB values.
     */
    protected function darkenColor(array $rgb): array
    {
        // convert to HSL
        $hsl = $this->rgbToHsl($rgb[0], $rgb[1], $rgb[2]);
        // lower L by 40%
        $hsl["l"] = $hsl["l"] - 0.5;
        if ($hsl["l"] < 0) {
            $hsl["l"] = 0;
        }
        // lower S by 10%
        $hsl["s"] = $hsl["s"] - 0.15;
        if ($hsl["s"] < 0) {
            $hsl["s"] = 0;
        }
        // convert HSL to RGB
        return $this->hslToRgb($hsl["h"], $hsl["s"], $hsl["l"]);
    }

    /**
     * Create a randomIsh RGB color
     * @param int loop The number of times to loop.
     * @return int[] An array of three integers, each between 0 and 255.
     */
    protected function createRGBColor(int $posLoop): array
    {
        $h = 0;
        while ($posLoop >= 0) {
            $h += 55;
            $posLoop--;
        }
        while ($h > 360) {
            $h -= 360;
        }
        return $this->hslToRgb($h, 0.55, 0.7);
    }


    /**
     * Convert RGB to HSL
     * @param r red value
     * @param g the green component of the color, in the range [0, 255]
     * @param b blue
     * @return int[] An array of three values: the hue, the saturation, and the lightness.
     */
    protected function rgbToHsl($r, $g, $b): array
    {
        $oldR = $r;
        $oldG = $g;
        $oldB = $b;

        $r /= 255;
        $g /= 255;
        $b /= 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);

        $h;
        $s;
        $l = ( $max + $min ) / 2;
        $d = $max - $min;

        if ($d == 0) {
            $h = $s = 0; // achromatic
        } else {
            $s = $d / ( 1 - abs(2 * $l - 1) );

            switch ($max) {
                case $r:
                    $h = 60 * fmod(( ( $g - $b ) / $d ), 6);
                    if ($b > $g) {
                        $h += 360;
                    }
                    break;

                case $g:
                    $h = 60 * ( ( $b - $r ) / $d + 2 );
                    break;

                case $b:
                    $h = 60 * ( ( $r - $g ) / $d + 4 );
                    break;
            }
        }

        return [ "h" => round($h, 2), "s" => round($s, 2), "l" => round($l, 2) ];
    }

    /**
     * Given an HSL color, convert it to RGB
     * @param h Hue value between 0 and 360
     * @param s saturation
     * @param l lightness
     * @return int[] An array of three numbers, each between 0 and 255.
     */
    protected function hslToRgb(int $h, float $s, float $l): array
    {
        $r;
        $g;
        $b;

        $c = ( 1 - abs(2 * $l - 1) ) * $s;
        $x = $c * ( 1 - abs(fmod(( $h / 60 ), 2) - 1) );
        $m = $l - ( $c / 2 );

        if ($h < 60) {
            $r = $c;
            $g = $x;
            $b = 0;
        } elseif ($h < 120) {
            $r = $x;
            $g = $c;
            $b = 0;
        } elseif ($h < 180) {
            $r = 0;
            $g = $c;
            $b = $x;
        } elseif ($h < 240) {
            $r = 0;
            $g = $x;
            $b = $c;
        } elseif ($h < 300) {
            $r = $x;
            $g = 0;
            $b = $c;
        } else {
            $r = $c;
            $g = 0;
            $b = $x;
        }

        $r = ( $r + $m ) * 255;
        $g = ( $g + $m ) * 255;
        $b = ( $b + $m  ) * 255;

        return [floor($r), floor($g), floor($b)];
    }

    /**
     * Creates a dataset entry for a donut chart
     * @param array dataset The dataset to be added to the chart.
     */
    protected function createDatasetEntryDonut(array $dataset): void
    {
        $backgroundColors = [];
        if (array_key_exists("backgroundColors", $dataset) == false) {
            $dataset["backgroundColors"] = [];
            foreach ($dataset["data"] as $point) {
                $dataset["backgroundColors"][] = $this->createRGBColor($this->posIndexColor);
                $this->posIndexColor++;
            }
        }

        foreach ($dataset["backgroundColors"] as $bgcolor) {
            $backgroundColors[] = "rgb(" . implode(",", $bgcolor) . ")";
        }

        if (array_key_exists("hoverOffset", $dataset) == false) {
            $dataset["hoverOffset"] = 4;
        }

        $this->jsonPacked["data"]["datasets"][] = [
            'label' => $dataset["title"],
            'data' => $dataset["data"],
            'hoverOffset' => $dataset["hoverOffset"],
            'backgroundColor' => $backgroundColors,
        ];
    }


    /**
     * Redirects to the correct processer for each chart type
     * @param string chartType The type of chart you want to create.
     * @param array dataset The dataset array.
     */
    protected function createDatasetEntry(string $chartType, array $dataset): void
    {
        if ($chartType == "line") {
            $this->createDatasetEntryLine($dataset);
        } elseif ($chartType == "doughnut") {
            $this->createDatasetEntryDonut($dataset);
        }
    }

    /**
     * Create the options for the chart
     * @param string chartType The type of chart you want to create.
     */
    protected function createChartOptions(string $chartType): void
    {
        if ($chartType == "line") {
            $this->createLineOptions();
        } elseif ($chartType == "doughnut") {
            $this->createDoughnutOptions();
        }
        $this->customize();
    }

    protected function createDoughnutOptions(): void
    {
        $this->jsonPacked["options"] = [
            "responsive" => true,
            "legend" => [
                "display" => true,
            ],
        ];
    }

    protected function createLineOptions(): void
    {
        $scales = [];
        $scales["yAxes"] = [];
        $scales["yAxes"][] = [
            "id" => "y",
            "position" => "left",
            "display" => true,
            "ticks" => [
                "reverse" => false,
                "fontColor" => "#CBCBCB",
            ],
        ];
        $scales["yAxes"][] = [
            "id" => "y2",
            "position" => "right",
            "display" => $this->secondYaxis,
            "ticks" => [
                "reverse" => false,
                "fontColor" => "#CBCBCB",
            ],
        ];
        $scales["xAxes"] = [];
        $scales["xAxes"][] = [
            "display" => true,
            "ticks" => [
                "reverse" => false,
                "fontColor" => "#CBCBCB",
            ],
        ];


        $this->jsonPacked["options"] = [
            "responsive" => true,
            "legend" => [
                "display" => true,
                "labels" => [
                    "fontColor" => "black",
                    "fontSize" => 14,
                ],
            ],
            "scales" => $scales,
        ];
    }

    protected function customize(): void
    {
        if (array_key_exists("noLabels", $this->customize) == true) {
            $this->jsonPacked["options"]["legend"]["display"] = false;
        }
    }

    protected function crateChartDataset(string $chartType, array $dataSets): void
    {
        if ($chartType == "doughnut") {
            $this->createDatasetEntry($chartType, $dataSets);
            return;
        }
        $this->jsonPacked["data"]["datasets"] = [];
        foreach ($dataSets as $dataset) {
            $this->createDatasetEntry($chartType, $dataset);
        }
    }

    protected array $jsonPacked = [];
    protected array $customize = [];
    /**
     * This function creates a chart, also puts the js needed onto html_js_onready make sure to call "getJS"
     * @param string chartid The ID of the chart.
     * @param array labels The labels for the x-axis.
     * @param array dataSets An array of data sets. Each data set is an array of data points.
     * @param string width The width of the chart in pixels.
     * @param string height The height of the chart in pixels.
     * @param string chartType The type of chart you want to create.
     * @param bool selfTriggerChart If you want to trigger the chart creation on the page itself, set this
     * to true.
     * @return string The HTML code for the chart.
     */
    public function createChart(
        string $chartid,
        array $labels,
        array $dataSets,
        string $width,
        string $height,
        string $chartType = 'line',
        bool $selfTriggerChart = true,
        array $customConfig = []
    ): string {
        $this->jsonPacked = [
            "chartID" => $chartid,
            'type' => $chartType,
            'data' => [
                'labels' => $labels,
            ],
        ];
        $this->customize = $customConfig;
        $this->crateChartDataset($chartType, $dataSets);
        $this->createChartOptions($chartType, $customConfig);

        if ($selfTriggerChart == true) {
            $this->html_js_onready .= "var " . $chartid . "options = JSON.parse('" .
            json_encode($this->jsonPacked) . "');\n";
            $this->html_js_onready .= "var " . $chartid . "object = document . getElementById('Chart"
            . $chartid . "') . getContext('2d');\n";
            $this->html_js_onready .= "new Chart(" . $chartid . "object, " . $chartid . "options);\n";
        } else {
            $this->chartNames[] = "Chart" . $chartid;
            $this->chartDataSets["Chart" . $chartid] = $this->jsonPacked;
        }
        return '<br/><canvas id="Chart'
        . $chartid . '" style="max-width: ' . $width . 'px; max-height: ' . $height . 'px;"></canvas>';
    }

    /*
        Demo functions
    */
    public function demoLineChart(): string
    {
        $guests = [
            "title" => "Guests",
            "data" => [6,5,4,3,2,1,7],
        ];
        $views = [
            "title" => "Views",
            "data" => [60,50,40,30,20,10,70],
        ];
        $dataSets = [];
        $dataSets[] = $guests;
        $dataSets[] = $views;
        return $this->createChart("demo", ["mon","tue","wed","thur","fri","sat","sun"], $dataSets, 500, 300);
    }
    public function demoPieChart(): string
    {
        $regions = [
            "title" => "Regions data",
            "data" => [55,15,35],
        ];
        $dataSets = [];
        $dataSets[] = $regions;
        return $this->createChart("demopie", ["USA","GB","AU"], $dataSets, 500, 300, 'doughnut');
    }
}
