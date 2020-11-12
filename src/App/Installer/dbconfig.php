<?php

if (defined("CORRECT") == false) {
    die("Error");
}
if (file_exists("../shared/config/db_installed.php") == true) {
    $view_reply->set_swap_tag_string("page_content", '<a href="test"><button class="btn btn-primary btn-block" type="button">Test</button></a>');
} else {
    if (getenv('DB_HOST') !== false) {
        $view_reply->set_swap_tag_string("page_content", '<a href="test"><button class="btn btn-primary btn-block" type="button">Test</button></a>');
    } else {
        $db_user = $input->postFilter("db_user");
        if ($db_user != null) {
            $keys = array(
                "DB_HOST_HERE" => $input->postFilter("db_host"),
                "DB_NAME_HERE" => $input->postFilter("db_name"),
                "DB_USER_HERE" => $db_user,
                "DB_PASSWORD_HERE" => $input->postFilter("db_pass"),
            );
            $db_config = file_get_contents("installer/db.tmp.php");
            foreach ($keys as $key => $value) {
                $db_config = str_replace("[[" . $key . "]]", $value, $db_config);
            }
            file_put_contents("../shared/config/db_installed.php", $db_config);
            $view_reply->set_swap_tag_string("page_content", '<a href="test"><button class="btn btn-primary btn-block" type="button">Test</button></a>');
        } else {
            $view_reply->set_swap_tag_string("page_content", '
            <div class="card border border-success rounded">
              <div class="card-body">
                <h5 class="card-title">DB config<br/>
                    <form action="" method="post">
                    <div class="row mt-4">
                        <div class="col-8 offset-2"><input name="db_host" class="form-control" type="text" placeholder="host" value="localhost"></div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-8 offset-2"><input name="db_name" class="form-control" type="text" placeholder="database" value="streamadmin"></div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-8 offset-2"><input name="db_user" class="form-control" type="text" placeholder="user" value="stradmusr"></div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-8 offset-2"><input name="db_pass" class="form-control" type="password" placeholder="password" value=""></div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-6"><button class="btn btn-primary btn-block" type="submit">Continue</button></div>
                    </div>
                    </form>
                </div>
            </div>');
        }
    }
}
