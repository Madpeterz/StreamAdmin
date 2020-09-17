<?php
$treevender_packages = new treevender_packages();
if($treevender_packages->load($page) == true)
{
    $treevender = new treevender();
    if($treevender->load($treevender_packages->get_treevenderlink()) == true)
    {
        $package = new package();
        if($package->load($treevender_packages->get_packagelink()) == true)
        {
            $template_parts["html_title"] .= " ~ Remove";
            $template_parts["page_title"] .= " Remove linked package:".$package->get_name()." from tree vender:";
            $template_parts["page_title"] .= $treevender->get_name();

            $template_parts["page_actions"] = "";
            $form = new form();
            $form->target("tree/removepackage/".$page."");
            $form->required(true);
            $form->col(6);
            $form->group("Warning");
            $form->text_input("accept","Type \"Accept\"",30,"","This will remove the link to the package");
            echo $form->render("Remove","danger");
        }
        else
        {
            redirect("tree?bubblemessage=Unable to find package&bubbletype=warning");
        }
    }
    else
    {
        redirect("tree?bubblemessage=Unable to find treevender thats linked to this package link&bubbletype=warning");
    }
}
else
{
    redirect("tree?bubblemessage=Unable to find linked treevender package&bubbletype=warning");
}


?>
