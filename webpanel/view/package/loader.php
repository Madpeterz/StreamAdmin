<?php
$template = new template();
if($template->HasAny() == true)
{
    $view_reply->set_swap_tag_string("html_title","Packages");
    $view_reply->set_swap_tag_string("page_title","[[page_breadcrumb_icon]] [[page_breadcrumb_text]] / ");
    $view_reply->set_swap_tag_string("page_actions","<a href='[[url_base]]package/create'><button type='button' class='btn btn-success'>Create</button></a>");
}
else
{
    $view_reply->redirect("template?message=Please create a template before creating a package");
}
?>
