<?php
function render()
{
    global $page, $optional, $module, $template_parts;
    $buffer = ob_get_contents();
    ob_clean();
    $template_parts["page_content"] = ob_get_contents();
    ob_clean();
    foreach($template_parts as $key => $value)
    {
        $buffer = str_replace("[[".$key."]]",$value,$buffer);
    }
    $buffer = str_replace("[[MODULE]]",$module,$buffer);
    $buffer = str_replace("[[AREA]]",$optional,$buffer);
    $buffer = str_replace("[[PAGE]]",$page,$buffer);
    foreach($template_parts as $key => $value)
    {
        $buffer = str_replace("[[".$key."]]",$value,$buffer);
    }
    $buffer = str_replace("[[MODULE]]",$module,$buffer);
    $buffer = str_replace("[[AREA]]",$optional,$buffer);
    $buffer = str_replace("[[PAGE]]",$page,$buffer);
    $buffer = str_replace("@NL@","\r\n",$buffer);
    echo $buffer;
}
if(defined("correct") == true)
{
    include("site/framework/core.php");
    include("installer/config.php");
    add_vendor("website");
    load_template("install");
    $input = new inputFilter();
    if($module == "owner")
    {
        include("installer/owner.php");
    }
    else if($module == "test")
    {
        include("installer/test.php");
    }
    else if($module == "install")
    {
        render();
        include("installer/install.php");
    }
    else if($module == "setup")
    {
        include("installer/setup.php");
    }
    else if($module == "updates")
    {
        include("installer/updates.php");
    }
    else if($module == "final")
    {
        include("installer/final.php");
    }
    else if($module == "patch")
    {
        include("installer/patch.php");
    }
    else
    {
        include("installer/dbconfig.php");
    }
}
else
{
    echo "Please do not attempt to run installer directly it will break something!";
}
render();

?>
