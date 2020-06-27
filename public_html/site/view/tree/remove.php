<?php
$template_parts["html_title"] .= " ~ Remove";
$template_parts["page_title"] .= " Remove tree:";
$template_parts["page_title"] .= $page;
$template_parts["page_actions"] = "";
if(in_array($page,array(6,10)) == false)
{
    $form = new form();
    $form->target("tree/remove/".$page."");
    $form->required(true);
    $form->col(6);
    $form->group("Warning");
    $form->text_input("accept","Type \"Accept\"",30,"","This will delete the tree vender");
    echo $form->render("Remove","danger");
    echo "";
}
else
{
    redirect("notice?bubblemessage=This notice is protected&bubbletype=warning");
}
?>
