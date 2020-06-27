<?php
$template_parts["html_title"] .= " ~ Remove";
$template_parts["page_title"] .= " Remove notice:";
$template_parts["page_title"] .= $page;
$template_parts["page_actions"] = "";
if(in_array($page,array(6,10)) == false)
{
    $form = new form();
    $form->target("notice/remove/".$page."");
    $form->required(true);
    $form->col(6);
    $form->group("Warning</h4><p>If the notice currenly in use this will fail</p><h4>");
    $form->text_input("accept","Type \"Accept\"",30,"","This will delete the texture pack");
    echo $form->render("Remove","danger");
    echo "";
}
else
{
    redirect("notice?bubblemessage=This notice is protected&bubbletype=warning");
}
?>
