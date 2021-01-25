<?php

namespace App\Endpoint\View\Install;

use App\Models\Avatar;
use App\Models\Slconfig;
use App\Models\Staff;
use App\Template\Form;
use YAPF\InputFilter\InputFilter;
use YAPF\MySQLi\MysqliEnabled;

class Setup extends View
{
    public function process(string $custom_write_config_location = null): void
    {
        parent::process();
        $this->setSwapTag("page_content", "");
        $this->setSwapTag("html_title", "Installer / Step 4 / System setup");
        $this->setSwapTag("page_title", "Installer / Step 4 / System setup");
        $input = new InputFilter();
        $form_ok = false;
        if ($input->postFilter("av_uuid", "uuid") != null) {
            $form_ok = $this->processForm($custom_write_config_location);
            if ($form_ok == true) {
                $this->nextAction();
                return;
            }
            $this->sql->sqlRollBack(true);
        }
        $this->setupForm();
    }

    protected function nextAction(): void
    {
        $this->setSwapTag(
            "page_content",
            '
            <div class="alert alert-success" role="alert">User config applyed
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
            </div><br/>
            <a href="final"><button class="btn btn-primary btn-block" type="button">Final changes</button></a>'
        );
    }

    protected function setupForm(): void
    {
        $form = new Form();
        $form->noAjax();
        $form->mode("post");
        $form->target("setup");
        $form->group("System setup");
        if (getenv('DB_HOST') === false) {
            $form->textInput("domain", "Domain", 120, "[[url_base]]", "Site URL (Dont forget the ending /)");
            $form->textInput("sitename", "Site name", 120, "StreamAdmin R7", "StreamAdmin R7");
        } else {
            $form->hiddenInput("domain", "Domain", 120, "skip", "skip");
            $form->hiddenInput("sitename", "Site name", 120, "skip", "skip");
        }
        $form->split();
        $form->textInput("av_username", "Login username", 120, "", "Does not have to match SL name");
        $form->uuidInput("av_uuid", "Avatar UUID", "", "Secondlife avatar UUID");
        $form->textInput("av_name", "Avatar name", 120, "", "Secondlife Resident");
        $form->split();
        $form->textInput("av_email", "Email address", 120, "", "recovery@email.address.com");
        $mainform = $form->render("Finalize", "primary");

        $this->output->addSwapTagString("page_content", $mainform . '
            <br/>
            <br/><br/><br/><hr/><p>Do not use this option unless told to!</p>
            <a href="finalstep">
            <button class="btn btn-warning btn-block" type="button">Skip setup goto final</button></a><br/>');
    }

    protected function processForm(string $custom_write_config_location = null): bool
    {
        $this->sql = new MysqliEnabled();
        $input = new InputFilter();
        $av_uuid = $input->postFilter("av_uuid", "uuid");
        $staff = new Staff();
        if ($staff->loadID(1) == false) {
            $this->setSwapTag("page_content", "unable to load staff entry");
            return false;
        }
        $staff->setUsername($input->postFilter("av_username"));
        $staff->setEmail($input->postFilter("av_email"));
        $update_status = $staff->updateEntry();
        if ($update_status["status"] == false) {
            $this->setSwapTag("page_content", "unable to update staff entry");
            return false;
        }

        $avatar = new Avatar();
        if ($avatar->loadID(1) == false) {
            $this->setSwapTag("page_content", "Unable to load avatar entry");
            return false;
        }
        $avatar->setAvatarUUID($av_uuid);
        $avatar->setAvatarName($input->postFilter("av_name"));
        $avatar->setAvatarUid($avatar->createUID("avatarUid", 8)["uid"]);
        $update_status = $avatar->updateEntry();
        if ($update_status["status"] == false) {
            $this->setSwapTag("page_content", "Unable to update avatar entry");
            return false;
        }

        $slconfig = new Slconfig();
        if ($slconfig->loadID(1) == false) {
            $this->setSwapTag("page_content", "Unable to load config entry");
            return false;
        }
        $slconfig->setSlLinkCode($slconfig->createUID("slLinkCode", 10, 10)["uid"]);
        $update_status = $slconfig->updateEntry();
        if ($update_status["status"] == false) {
            $this->setSwapTag("page_content", "Unable to update config entry");
            return false;
        }

        return $this->writeConfigFile($custom_write_config_location);
    }

    protected function writeConfigFile(string $custom_write_config_location = null): bool
    {
        $input = new InputFilter();
        if ($input->postFilter("domain") == "skip") {
            return true;
        }
        $content = '<?php

namespace App;

$site_theme = "streamadminr5";
$site_lang = "en";
$template_parts["html_title"] = " Page ";
$template_parts["html_title_after"] = "[[INSTALL_SITE_NAME]]";
$template_parts["url_base"] = "[[INSTALL_SITE_URI]];
';
        $content = str_replace("[[INSTALL_SITE_NAME]]", $input->postFilter("sitename"), $content);
        $content = str_replace("[[INSTALL_SITE_URI]]", $input->postFilter("domain"), $content);
        $config_file = "../App/Config/site_installed.php";
        if ($custom_write_config_location != null) {
            $config_file = $custom_write_config_location;
        }
        if (file_exists($config_file) == true) {
            unlink($config_file);
        }
        return file_put_contents($config_file, $content);
    }
}
