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
if($session->get_ownerlevel() == 1)
{
    $template_parts["page_title"] = "Updates";
    $template_parts["page_actions"] = "";
    $template_parts["html_title"] = "Updates";
    $template_parts["page_actions"] = "";
    if(file_exists("versions/sql/".$slconfig->get_db_version().".sql") == true)
    {
        render();
        $status = $sql->RawSQL("versions/sql/".$slconfig->get_db_version().".sql",true);
        if($status["status"] == true)
        {
            $slconfig = new slconfig();
            $slconfig->load(1);
            $modal_folder = "site/model";
            $update_modal_folder = "versions/modal/".$slconfig->get_db_version()."";
            if(file_exists($update_modal_folder."/required.txt") == true)
            {
                $scanned_directory = array_diff(scandir($update_modal_folder), array('..', '.','required.txt'));
                foreach($scanned_directory as $entry)
                {
                    unlink($modal_folder."/".$entry);
                    copy($update_modal_folder."/".$entry,$modal_folder."/".$entry);
                }
                echo "Ok";
            }
            else
            {
                echo "Ok";
            }
        }
        else
        {
            $sql->sqlRollBack();
            echo "Issue detected: <hr/>".$status["message"]."<br/>Unable to update";
        }
    }
    else
    {
        redirect("?message=no updates");
    }
}
else
{
    redirect("?message=missing perm");
}
?>
