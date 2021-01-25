<?php

namespace App\Template;

class Cache extends AddonProvider
{
    protected $catche_version = "";
    public function setCacheFile(string $content, string $name, bool $with_module_tag = true): void
    {
        global $module;
        $this->getCatcheVersion();
        $filename = $name;
        if ($with_module_tag == true) {
            $filename .= $module;
        }
        $filename = base64_encode($filename);
        file_put_contents("catche/" . $filename, $content);
    }
    public function purgeCacheFile(string $name, bool $with_module_tag = true): bool
    {
        global $module;
        $this->getCatcheVersion();
        $filename = $name;
        if ($with_module_tag == true) {
            $filename .= $module;
        }
        $filename = base64_encode($filename);
        if (file_exists("catche/" . $filename) == true) {
            unlink("catche/" . $filename);
            if (file_exists("catche/" . $filename) == true) {
                return false;
            }
        }
        return true;
    }
    public function getCacheFile(string $name, bool $with_module_tag = true): ?string
    {
        global $module;
        $this->getCatcheVersion();
        $filename = $name;
        if ($with_module_tag == true) {
            $filename .= $module;
        }
        $filename = "catche/" . base64_encode($filename);
        if (file_exists($filename) == true) {
            return file_get_contents($filename);
        } else {
            return null;
        }
    }
    protected function createCacheVersionFile(): void
    {
        global $slconfig;
        if (is_dir("catche") == false) {
            mkdir("catche");
        }
        if (file_exists("catche/version.info") == false) {
            file_put_contents("catche/version.info", $slconfig->getDbVersion());
            $this->catche_version = $slconfig->getDbVersion();
        } else {
            $this->catche_version = file_get_contents("catche/version.info");
        }
    }
    public function getCatcheVersion(): ?string
    {
        if ($this->catche_version == null) {
            $this->createCacheVersionFile();
        }
        $this->purgeCache();
        return $this->catche_version;
    }
    public function purgeCache(): void
    {
        global $slconfig;
        if ($this->catche_version != null) {
            if (version_compare($slconfig->getDbVersion(), $this->catche_version) == 1) {
                // DB is newer force reload cache
                $this->delTree("catche");
                $this->createCacheVersionFile();
            }
        } else {
            // force clear cache
            $this->delTree("catche");
            $this->createCacheVersionFile();
        }
    }

    protected function delTree($dir): bool
    {
        $files = array_diff(scandir($dir), ['.','..']);
        foreach ($files as $file) {
            if (is_dir($dir . "/" . $file) == true) {
                $result = $this->delTree($dir . "/" . $file);
                if ($result == false) {
                    return false;
                }
            } else {
                unlink($dir . "/" . $file);
            }
        }
        return rmdir($dir);
    }
}
