<?php
if($session->get_ownerlevel() == true)
{
    $template_parts["html_title"] .= " ~ Remove";
    $template_parts["page_title"] .= "Remove staff member: ";
    $template_parts["page_title"] .= $page;
    $template_parts["page_actions"] = "";

    $form = new form();
    $form->target("staff/remove/".$page."");
    $form->required(true);
    $form->col(6);
    $form->group("Warning</h4><p>The web interface will not allow you to remove owner level accounts!</p><h4>");
    $action = '
    <div class="btn-group btn-group-toggle" data-toggle="buttons">
      <label class="btn btn-outline-danger active">
        <input type="radio" value="Accept" name="accept" autocomplete="off" > Accept
      </label>
      <label class="btn btn-outline-secondary">
        <input type="radio" value="Nevermind" name="accept" autocomplete="off" checked> Nevermind
      </label>
    </div>';
    $form->direct_add($action);
    print $form->render("Remove","danger");
    print "";
}
else
{
    redirect("staff?bubblemessage=Owner level access needed&bubbletype=warning");
}
?>
