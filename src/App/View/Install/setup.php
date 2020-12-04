<?php

if (defined("correct") == false) {
    die("Error");
}
    $av_uuid = $input->postFilter("av_uuid", "uuid");
    $load_ok = false;
if ($av_uuid != null) {
    include "../App/Config/db.php";
    require_once("shared/framework/mysqli/src/loader.php"); // sql_driver
    $sql = new mysqli_controler();
    $staff = new staff();
    if ($staff->loadID(1) == true) {
        $staff->set_username($input->postFilter("av_username"));
        $staff->set_email($input->postFilter("av_email"));
        $update_status = $staff->save_changes();
        if ($update_status["status"] == true) {
            $avatar = new avatar();
            if ($avatar->loadID(1) == true) {
                $avatar->set_avataruuid($av_uuid);
                $avatar->set_avatarname($input->postFilter("av_name"));
                $avatar->set_avatar_uid($avatar->create_uid("avatar_uid", 8, 10)["uid"]);
                $update_status = $avatar->save_changes();
                if ($update_status["status"] == true) {
                    $slconfig = new slconfig();
                    if ($slconfig->loadID(1) == true) {
                        $slconfig->set_sllinkcode($slconfig->create_uid("sllinkcode", 10, 10)["uid"]);
                        $update_status = $slconfig->save_changes();
                        if ($update_status["status"] == true) {
                            $load_ok = true;
                            $siteconfigok = true;
                            if ($input->postFilter("domain") != "skip") {
                                $content = '<?php
$site_theme = "streamadminr5";
$site_lang = "en";
$template_parts["html_title"] = " Page ";
$template_parts["html_title_after"] = "[[INSTALL_SITE_NAME]]";
$template_parts["url_base"] = "[[INSTALL_SITE_URI]]";
?>';
                                $content = str_replace("[[INSTALL_SITE_NAME]]", $input->postFilter("sitename"), $content);
                                $content = str_replace("[[INSTALL_SITE_URI]]", $input->postFilter("domain"), $content);
                                if (file_exists("../App/Config/site_installed.php") == true) {
                                    unlink("../App/Config/site_installed.php");
                                }
                                file_put_contents("../App/Config/site_installed.php", $content);
                            }
                            if ($siteconfigok == true) {
                                $sql->sqlSave(true);
                                $this->output->setSwapTagString("page_content", '<a href="final"><button class="btn btn-primary btn-block" type="button">Final changes</button></a>');
                            } else {
                                $sql->sqlRollBack(true);
                                $this->output->setSwapTagString("page_content", "Site config not vaild");
                            }
                        } else {
                            $sql->sqlRollBack(true);
                            $this->output->setSwapTagString("page_content", "Unable to update config entry");
                        }
                    } else {
                        $sql->sqlRollBack(true);
                        $this->output->setSwapTagString("page_content", "Unable to load config entry");
                    }
                } else {
                    $sql->sqlRollBack(true);
                    $this->output->setSwapTagString("page_content", "Unable to update avatar entry");
                }
            } else {
                $sql->sqlRollBack(true);
                $this->output->setSwapTagString("page_content", "Unable to load avatar entry");
            }
        } else {
            $sql->sqlRollBack(true);
            $this->output->setSwapTagString("page_content", "unable to update staff entry");
        }
    } else {
        $sql->sqlRollBack(true);
        $this->output->setSwapTagString("page_content", "unable to load staff entry");
    }
}
if ($load_ok == false) {
    $this->output->addSwapTagString("page_content", '
    <div class="card border border-success rounded">
      <div class="card-body">
        <h5 class="card-title">Final setup<br/>
            <form action="setup" method="post">');
    if (getenv('DB_HOST') === false) {
        $this->output->addSwapTagString("page_content", '
                <div class="row mt-4">
                    <div class="col-8 offset-2"><input name="domain" class="form-control" type="text" placeholder="Site URL (Dont forget the ending /)" value="http://' . $_SERVER['HTTP_HOST'] . '/"></div>
                </div>
                <div class="row mt-4">
                    <div class="col-8 offset-2"><input name="sitename" class="form-control" type="text" placeholder="Site name" value="Streamadmin R7"></div>
                </div>
                ');
    } else {
        $this->output->addSwapTagString("page_content", '
                <div class="row mt-4">
                    <div class="col-8 offset-2"><input name="domain" class="form-control" type="hidden" placeholder="" value="skip"></div>
                </div>
                <div class="row mt-4">
                    <div class="col-8 offset-2"><input name="sitename" class="form-control" type="hidden" placeholder="" value="skip"></div>
                </div>
                ');
    }
        $this->output->addSwapTagString("page_content", '
            <div class="row mt-4">
                <div class="col-8 offset-2"><input name="av_username" class="form-control" type="text" placeholder="Username (Does not have to match SL name)" value=""></div>
            </div>
            <div class="row mt-4">
                <div class="col-8 offset-2"><input name="av_uuid" class="form-control" type="text" placeholder="UUID" value=""></div>
            </div>
            <div class="row mt-4">
                <div class="col-8 offset-2"><input name="av_name" class="form-control" type="text" placeholder="Secondlife Resident" value=""></div>
            </div>
            <div class="row mt-4">
                <div class="col-8 offset-2"><input name="av_email" class="form-control" type="text" placeholder="recovery@email.address.com" value=""></div>
            </div>
            <div class="row mt-3">
                <div class="col-6"><button class="btn btn-primary btn-block" type="submit">Finalize</button></div>
            </div>
            </form>
            <br/>
            <br/><br/><br/><hr/><p>Do not use this option unless told to!</p>
            <a href="final"><button class="btn btn-warning btn-block" type="button">Skip setup goto final</button></a><br/>
        </div>
    </div>');
}
