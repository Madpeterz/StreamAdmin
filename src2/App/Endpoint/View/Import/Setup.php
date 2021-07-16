<?php

namespace App\Endpoint\View\Import;

use App\Template\Form;

class Setup extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("html_title", " ~ Setup R4 connection");
        $this->setSwapTag("page_title", "R4 DB settings");
        $form = new Form();
        $form->target("import/setconfig");
        $form->required(true);
        $form->col(6);
        $form->group("DB");

        $useDB = "";
        $useUsername = "";
        $usePass = "";
        $useHost = "";

        if (file_exists("" . ROOTFOLDER . "/App/Config/r4.php") == true) {
            include "" . ROOTFOLDER . "/App/Config/r4.php";
            $useHost = $r4_db_host;
            $useUsername = $r4_db_username;
            $usePass = $r4_db_pass;
            $useDB = $r4_db_name;
        }
        $form->textInput("db_host", "Host", 999, $useHost, "Host");
        $form->textInput("db_name", "Name", 999, $useDB, "Database name");
        $form->textInput("db_username", "Username", 999, $useUsername, "Database username");
        $form->textInput("db_pass", "Password", 999, $usePass, "Database password");
        $this->output->addSwapTagString("page_content", $form->render("Setup", "primary"));
    }
}
