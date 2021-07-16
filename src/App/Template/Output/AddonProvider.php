<?php

namespace App\Template\Output;

abstract class AddonProvider extends SwapTags
{
    protected $registered_vendors = [];
    protected function proccessAddRequest(
        string $provider_name = "",
        $provider = [],
        array $sub_class = [],
        $target_group = "css",
        $target_function = "add_css_to_page"
    ): void {
        if (array_key_exists($target_group, $provider) == true) {
            if (array_key_exists("main_file", $provider[$target_group]) == true) {
                if (is_array($provider[$target_group]["main_file"]) == true) {
                    foreach ($provider[$target_group]["main_file"] as $css_main_file) {
                        $this->$target_function($provider_name, $provider, $css_main_file);
                    }
                } else {
                    $this->$target_function(
                        $provider_name,
                        $provider,
                        $provider[$target_group]["main_file"]
                    );
                }
            }
            foreach ($sub_class as $sub_class_entry) {
                if (array_key_exists($sub_class_entry, $provider[$target_group]["subtypes"]) == true) {
                    if (is_array($provider[$target_group]["subtypes"][$sub_class_entry]) == true) {
                        foreach ($provider[$target_group]["subtypes"][$sub_class_entry] as $css_sub_file) {
                            $this->$target_function($provider_name, $provider, $css_sub_file);
                        }
                    } else {
                        $this->$target_function(
                            $provider_name,
                            $provider,
                            $provider[$target_group]["subtypes"][$sub_class_entry]
                        );
                    }
                }
            }
        }
    }
    protected function onAdd(string $provider): void
    {
        if ($provider == "datatable") {
            $this->addSwapTagString("html_js_onready", "
        $('.datatable-default').DataTable({
          'order': [[ 0, 'desc' ]],
          responsive: true,
        ");
            if (version_compare($this->slconfig->getDbVersion(), "1.0.0.4", ">") == true) {
                $this->addSwapTagString("html_js_onready", "
                pageLength: " . $this->slconfig->getDatatableItemsPerPage() . ",
                lengthMenu: [[" . $this->slconfig->getDatatableItemsPerPage() . ", "
                . "10, 25, 50, -1], [\"Custom\", 10, 25, 50, \"All\"]],
                ");
            }
            $this->addSwapTagString("html_js_onready", "
          language: {
            searchPlaceholder: 'Search...',
            sSearch: '',
            lengthMenu: '_MENU_ items/page',
            },
           'columnDefs': [
                {
                    'targets': [ 0 ],
                    'visible': false,
                    'searchable': false
                }]
        });");
        }
    }
    public function addVendor(
        string $provider_name = "",
        array $sub_class = [],
        bool $css_only = false,
        bool $js_only = false
    ): void {
        $providers = [
        "website" => [
            "require_before" => [
                "jquery" => [],
                "bootstrap" => [],
                "popper" => [],
                "fontawesome" => ["all"],
                "bootstrap-notify" => [],
            ],
        ],
        "datatable" => [
            "main_folder" => "datatables",
            "js" => [
                "main_file" => "datatables.min",
            ],
            "css" => [
                "main_file" => "datatables.min",
            ],
        ],
        "inputmask" => [
            "main_folder" => "inputmask",
            "js" => [
                "main_file" => "jquery.inputmask.min",
            ],
        ],
        "jquery" => [
            "main_folder" => "jquery",
            "js" => [
                "main_file" => "jquery-3.4.1.min", // jquery.min.js
            ],
        ],
        "bootstrap" => [
            "main_folder" => "bootstrap-4.4.1-dist",
            "js" => [
                "main_file" => "bootstrap.bundle.min",
                "sub_folder" => "js",
            ],
            "css" => [
                "main_file" => "bootstrap.min",
                "sub_folder" => "css",
            ],
        ],
        "popper" => [
            "main_folder" => "popper",
            "js" => [
                "main_file" => "popper.min",
            ],
        ],
        "fontawesome" => [
            "main_folder" => "fontawesome-free-5.12.1-web",
            "css" => [
                "sub_folder" => "css",
                "main_file" => "fontawesome.min",
                "subtypes" => [
                    "all" =>  "all.min",
                ],
            ],
        ],
        "bootstrap-notify" => [
            "main_folder" => "bootstrap-notify-3.1.3",
            "js" => [
                "main_file" => "bootstrap-notify.min",
            ],
        ],
        ];
        if (array_key_exists($provider_name, $providers) == true) {
            if (array_key_exists($provider_name, $this->registered_vendors) == false) {
                $this->registered_vendors[$provider_name] = ["css" => [],"js" => []];
                if (array_key_exists("require_before", $providers[$provider_name]) == true) {
                    foreach ($providers[$provider_name]["require_before"] as $key => $value) {
                        $this->addVendor($key, $value);
                    }
                }
                $this->onAdd($provider_name);
                if ($css_only == false) {
                    $this->proccessAddRequest(
                        $provider_name,
                        $providers[$provider_name],
                        $sub_class,
                        "js",
                        "addJsToPage"
                    );
                }
                if ($js_only == false) {
                    $this->proccessAddRequest(
                        $provider_name,
                        $providers[$provider_name],
                        $sub_class,
                        "css",
                        "addCssToPage"
                    );
                }
            }
        }
    }
    protected function addCssToPage(string $provider_name, array $provider, string $file = ""): void
    {
        $load_path = "[[url_base]]3rdparty/" . $provider["main_folder"] . "";
        if (array_key_exists("local_folder", $provider) == true) {
            $load_path .= "/" . $provider["local_folder"] . "";
        }
        if (array_key_exists("sub_folder", $provider["css"]) == true) {
            $load_path .= "/" . $provider["css"]["sub_folder"];
        }
        if (array_key_exists($provider_name, $this->registered_vendors) == true) {
            if (array_key_exists("js", $this->registered_vendors[$provider_name]) == true) {
                if (array_key_exists($file, $this->registered_vendors[$provider_name]["js"]) == false) {
                    $this->registered_vendors[$provider_name]["css"][] = $file;
                    $this->addSwapTagString(
                        "html_cs_top",
                        '<link rel="stylesheet" type="text/css" href="' . $load_path . '/' . $file . '.css">'
                    );
                }
            }
        }
    }
    protected function addJsToPage(string $provider_name, array $provider, string $file = ""): void
    {
        $load_path = "[[url_base]]3rdparty/" . $provider["main_folder"] . "";
        if (array_key_exists("local_folder", $provider) == true) {
            $load_path .= "/" . $provider["local_folder"] . "";
        }
        if (array_key_exists("sub_folder", $provider["js"]) == true) {
            $load_path .= "/" . $provider["js"]["sub_folder"];
        }
        if (array_key_exists($provider_name, $this->registered_vendors) == true) {
            if (array_key_exists("css", $this->registered_vendors[$provider_name]) == true) {
                if (array_key_exists($file, $this->registered_vendors[$provider_name]["css"]) == false) {
                    $this->registered_vendors[$provider_name]["js"][] = $file;
                    $this->addSwapTagString(
                        "html_js_bottom",
                        '<script src="' . $load_path . '/' . $file . '.js"></script>'
                    );
                }
            }
        }
    }
}
