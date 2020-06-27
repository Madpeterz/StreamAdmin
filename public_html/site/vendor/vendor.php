<?php
$registered_vendors = array();
function proccess_add_request(string $provider_name="",$provider=array(),array $sub_class=array(),$target_group="css",$target_function="add_css_to_page")
{
    if(array_key_exists($target_group,$provider) == true)
    {
        if(array_key_exists("main_file",$provider[$target_group]) == true)
        {
            if(is_array($provider[$target_group]["main_file"]) == true)
            {
                foreach($provider[$target_group]["main_file"] as $css_main_file)
                {
                    $target_function($provider_name,$provider,$css_main_file);
                }
            }
            else
            {
                $target_function($provider_name,$provider,$provider[$target_group]["main_file"]);
            }
        }
        foreach($sub_class as $sub_class_entry)
        {
            if(array_key_exists($sub_class_entry,$provider[$target_group]["subtypes"]) == true)
            {
                if(is_array($provider[$target_group]["subtypes"][$sub_class_entry]) == true)
                {
                    foreach($provider[$target_group]["subtypes"][$sub_class_entry] as $css_sub_file)
                    {
                        $target_function($provider_name,$provider,$css_sub_file);
                    }
                }
                else
                {
                    $target_function($provider_name,$provider,$provider[$target_group]["subtypes"][$sub_class_entry]);
                }
            }
        }
    }
}
function on_add(string $provider)
{
    global $template_parts;
    if($provider == "datatable")
    {
        $template_parts["html_js_onready"] .= "
        $('.datatable-default').DataTable({
          'order': [[ 0, 'desc' ]],
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
        });";
    }
}
function add_vendor(string $provider_name="",array $sub_class=array(),bool $css_only=false,bool $js_only=false)
{
    global $registered_vendors;
    $providers = array(
        "website" => array(
            "require_before" => array(
                "jquery" => array(),
                "bootstrap" => array(),
                "popper" => array(),
                "fontawesome" => array("all"),
                "bootstrap-notify" => array()
            )
        ),
        "datatable" => array(
            "main_folder" => "datatables",
            "js" => array(
                "main_file" => "datatables.min",
            ),
            "css" => array(
                "main_file" => "datatables.min",
            )
        ),
        "inputmask" => array(
            "main_folder" => "inputmask",
            "js" => array(
                "main_file" => "jquery.inputmask.min",
            )
        ),
        "jquery" => array(
            "main_folder" => "jquery",
            "js" => array(
                "main_file" => "jquery-3.4.1.min", // jquery.min.js
            )
        ),
        "bootstrap" => array(
            "main_folder" => "bootstrap-4.4.1-dist",
            "js" => array(
                "main_file" => "bootstrap.bundle.min",
                "sub_folder" => "js",
            ),
            "css" => array(
                "main_file" => "bootstrap.min",
                "sub_folder" => "css",
            )
        ),
        "popper" => array(
            "main_folder" => "popper",
            "js" => array(
                "main_file" => "popper.min",
            ),
        ),
        "fontawesome" => array(
            "main_folder" => "fontawesome-free-5.12.1-web",
            "css" => array(
                "sub_folder" => "css",
                "main_file" => "fontawesome.min",
                "subtypes" => array(
                    "all" =>  "all.min",
                )
            )
        ),
        "bootstrap-notify" => array(
            "main_folder" => "bootstrap-notify-3.1.3",
            "js" => array(
                "main_file" => "bootstrap-notify.min",
            )
        ),
    );
    if(array_key_exists($provider_name,$providers) == true)
    {
        if(array_key_exists($provider_name,$registered_vendors) == false)
        {
            $registered_vendors[$provider_name] = array("css" => array(),"js" => array());
            if(array_key_exists("require_before",$providers[$provider_name]) == true)
            {
                foreach($providers[$provider_name]["require_before"] as $key => $value)
                {
                    add_vendor($key,$value);
                }
            }
            on_add($provider_name);
            if($css_only == false)
            {
                // add JS
                proccess_add_request($provider_name,$providers[$provider_name],$sub_class,"js","add_js_to_page");
            }
            if($js_only == false)
            {
                proccess_add_request($provider_name,$providers[$provider_name],$sub_class,"css","add_css_to_page");
            }
        }
    }

}
function add_css_to_page(string $provider_name,array $provider,string $file="")
{
    global $template_parts, $registered_vendors;
    $load_path = "[[url_base]]site/vendor/".$provider["main_folder"]."";
    if(array_key_exists("local_folder",$provider) == true)
    {
        $load_path .= "/".$provider["local_folder"]."";
    }
    if(array_key_exists("sub_folder",$provider["css"]) == true) $load_path .= "/".$provider["css"]["sub_folder"];
    if(array_key_exists($file,$registered_vendors[$provider_name]["css"]) == false)
    {
        $registered_vendors[$provider_name]["css"][] = $file;
        $template_parts["html_cs_top"] .= '<link rel="stylesheet" type="text/css" href="'.$load_path.'/'.$file.'.css">';
    }
}
function add_js_to_page(string $provider_name,array $provider,string $file="")
{
    global $template_parts, $registered_vendors;
    $load_path = "[[url_base]]site/vendor/".$provider["main_folder"]."";
    if(array_key_exists("local_folder",$provider) == true)
    {
        $load_path .= "/".$provider["local_folder"]."";
    }
    if(array_key_exists("sub_folder",$provider["js"]) == true) $load_path .= "/".$provider["js"]["sub_folder"];
    if(array_key_exists($file,$registered_vendors[$provider_name]["css"]) == false)
    {
        $registered_vendors[$provider_name]["js"][] = $file;
        $template_parts["html_js_bottom"] .= '<script src="'.$load_path.'/'.$file.'.js"></script>';
    }
}
function add_css(string $provider_name="",array $sub_class=array())
{
    add_vendor($provider_name,$sub_class,true);
}
function add_js(string $provider_name="",array $sub_class=array())
{
    add_vendor($provider_name,$sub_class,false,true);
}
?>
