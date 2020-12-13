<?php

namespace App\View\Install;

use App\Template\Form;
use YAPF\InputFilter\InputFilter;

class DefaultView extends View
{
    public function process(): void
    {
        parent::process();
        $this->output->setSwapTagString("html_title", "Installer / Step 1 / DB config");
        $this->output->setSwapTagString("page_title", "Installer / Step 1 / DB config");
        $has_config = file_exists("../App/Config/db_installed.php");
        $has_env_config = (getenv('DB_HOST') !== false);
        if (($has_config == false) && ($has_env_config == false)) {
            $this->getConfigFromUser();
        } else {
            $this->getTestButton();
        }
    }
    protected function getTestButton(): void
    {
        $this->output->setSwapTagString(
            "page_content",
            '
            <div class="alert alert-success" role="alert">DB config ready
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
            </div><br/>
            <a href="test"><button class="btn btn-primary btn-block" type="button">Test config</button></a>'
        );
    }
    protected function getConfigFromUser(): void
    {
        $input = new InputFilter();
        $db_user = $input->postFilter("db_user");
        if ($db_user == null) {
            $this->DbConfigForm();
            return;
        }
        $keys = [
            "DB_HOST_HERE" => $input->postFilter("db_host"),
            "DB_NAME_HERE" => $input->postFilter("db_name"),
            "DB_USER_HERE" => $db_user,
            "DB_PASSWORD_HERE" => $input->postFilter("db_pass"),
        ];
        $db_config = file_get_contents("../App/View/Install/Required/db.tmp.php");
        foreach ($keys as $key => $value) {
            $db_config = str_replace("[[" . $key . "]]", $value, $db_config);
        }
        file_put_contents("../App/Config/db_installed.php", $db_config);
        $this->getTestButton();
    }
    protected function dbConfigForm(): void
    {
        $form = new Form();
        $form->mode("post");
        $form->target("");
        $form->noAjax();
        $form->required(true);
        $form->group("DB config");
        $form->textInput("db_host", "Host", 200, "localhost", "ip/domain to the host: Default localhost");
        $form->textInput("db_name", "Database", 200, "streamadmin", "the name of the database");
        $form->textInput("db_user", "User", 200, "", "the username for the database");
        $form->textInput("db_pass", "Password", 200, "", "Password", "", "password");
        $this->output->setSwapTagString("page_content", $form->render("Continue", "primary"));
    }
}
