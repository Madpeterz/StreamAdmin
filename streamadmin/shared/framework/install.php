<?php
function install_ok()
{
    if(getenv('DB_HOST') !== false)
    {
        if(getenv('INSTALL_OK') !== false)
        {
            if(getenv('INSTALL_OK') == 1)
            {
                return true;
            }
        }
    }
    return file_exists("ready.txt");
}
?>
