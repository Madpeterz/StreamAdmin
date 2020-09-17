<?php
if(defined("correct") == false) {die("Error");}
    $av_uuid = $input->postFilter("av_uuid","uuid");
    $load_ok = false;
    if($av_uuid != null)
    {
        include("site/config/db.php");
        require_once("site/framework/mysqli/src/loader.php"); // sql_driver
        $sql = new mysqli_controler();
        $staff = new staff();
        if($staff->load(1) == true)
        {
            $staff->set_username($input->postFilter("av_username"));
            $staff->set_email($input->postFilter("av_email"));
            $update_status = $staff->save_changes();
            if($update_status["status"] == true)
            {
                $avatar = new avatar();
                if($avatar->load(1) == true)
                {
                    $avatar->set_avataruuid($av_uuid);
                    $avatar->set_avatarname($input->postFilter("av_name"));
                    $avatar->set_avatar_uid($avatar->create_uid("avatar_uid",8,10)["uid"]);
                    $update_status = $avatar->save_changes();
                    if($update_status["status"] == true)
                    {
                        $slconfig = new slconfig();
                        if($slconfig->load(1) == true)
                        {
                            $slconfig->set_sllinkcode($slconfig->create_uid("sllinkcode",10,10)["uid"]);
                            $update_status = $slconfig->save_changes();
                            if($update_status["status"] == true)
                            {

                                $load_ok = true;
                                $siteconfigok = true;
                                if($input->postFilter("domain") != "skip")
                                {
$content = '<?php
$site_theme = "streamadminr5";
$site_lang = "en";
$template_parts["html_title"] = " Page ";
$template_parts["html_title_after"] = "[[INSTALL_SITE_NAME]]";
$template_parts["url_base"] = "[[INSTALL_SITE_URI]]";
?>';
                                    $content = str_replace("[[INSTALL_SITE_NAME]]",$input->postFilter("sitename"),$content);
                                    $content = str_replace("[[INSTALL_SITE_URI]]",$input->postFilter("domain"),$content);
                                    if(file_exists("site/config/site_installed.php") == true) unlink("site/config/site_installed.php");
                                    file_put_contents("site/config/site_installed.php",$content);
                                }
                                if($siteconfigok == true)
                                {
                                    $sql->sqlSave(true);
                                    ?>
                                    <a href="final"><button class="btn btn-primary btn-block" type="button">Final changes</button></a>
                                    <?php
                                }
                                else
                                {
                                    $sql->sqlRollBack(true);
                                    print "Site config not vaild";
                                }
                            }
                            else
                            {
                                $sql->sqlRollBack(true);
                                print "Unable to update config entry";
                            }
                        }
                        else
                        {
                            $sql->sqlRollBack(true);
                            print "Unable to load config entry";
                        }
                    }
                    else
                    {
                        $sql->sqlRollBack(true);
                        print "Unable to update avatar entry";
                    }
                }
                else
                {
                    $sql->sqlRollBack(true);
                    print "Unable to load avatar entry";
                }
            }
            else
            {
                $sql->sqlRollBack(true);
                print "unable to update staff entry";
            }
        }
        else
        {
            $sql->sqlRollBack(true);
            print "unable to load staff entry";
        }
    }
    if($load_ok == false)
    {
?>
    <div class="card border border-success rounded">
      <div class="card-body">
        <h5 class="card-title">Final setup<br/>
            <form action="setup" method="post">
            <?php
            if(getenv('DB_HOST') === false)
            {
                ?>
                <div class="row mt-4">
                    <div class="col-8 offset-2"><input name="domain" class="form-control" type="text" placeholder="Site URL (Dont forget the ending /)" value="http://<?php print $_SERVER['HTTP_HOST'];?>/"></div>
                </div>
                <div class="row mt-4">
                    <div class="col-8 offset-2"><input name="sitename" class="form-control" type="text" placeholder="Site name" value="Streamadmin R7"></div>
                </div>
                <?php
            }
            else
            {
                ?>
                <div class="row mt-4">
                    <div class="col-8 offset-2"><input name="domain" class="form-control" type="hidden" placeholder="" value="skip"></div>
                </div>
                <div class="row mt-4">
                    <div class="col-8 offset-2"><input name="sitename" class="form-control" type="hidden" placeholder="" value="skip"></div>
                </div>
                <?php
            }
            ?>
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
    </div>
<?php
    }
?>
