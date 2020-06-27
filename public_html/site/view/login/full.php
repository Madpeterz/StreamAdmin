<?php
if($area == "reset")
{
    $template_parts["html_title"] = "Reset password";
    include("site/theme/".$site_theme."/blocks/login/reset.layout");
}
else if($area == "resetwithtoken")
{
    $template_parts["html_title"] = "Recover password";
    include("site/theme/".$site_theme."/blocks/login/passwordrecover.layout");
}
else if($area == "logout")
{
    $session->end_session();
    redirect("");
}
else
{
    $template_parts["html_title"] = "Login";
    $template_parts["why_logged_out"] = $session->get_why_logged_out();
    include("site/theme/".$site_theme."/blocks/login/login.layout");
}

?>
