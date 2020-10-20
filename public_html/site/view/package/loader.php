<?php
$template = new template();
if($template->HasAny() == true)
{
    $template_parts["html_title"] = "Packages";
    $template_parts["page_actions"] = "<a href='[[url_base]]package/create'><button type='button' class='btn btn-success'>Create</button></a>";
    $template_parts["page_title"] = "[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / ";
}
else
{
    redirect("template?message=Please create a template before creating a package");
}
?>
