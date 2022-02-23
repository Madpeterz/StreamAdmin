<?php

namespace YAPF\Bootstrap\Template\Output;

use YAPF\Bootstrap\ConfigBox\BootstrapConfigBox;

abstract class SwapTags
{
    protected BootstrapConfigBox $config;
    protected $swaptags = [];
    public function __construct(bool $with_defaults = false)
    {
        global $system;
        $this->config = $system;
        if ($with_defaults == true) {
            $this->swaptags = [
            "@NL@" => "\r\n",
            "PAGE_TITLE" => "",
            "SITE_NAME" => "",
            "SITE_URL" => null,
            "META_TAGS" => "",
            "unixtimeNow" => time(),
            ];
        }
        $this->setSwapTag("SITE_URL", $this->config->getSiteURL());
    }
    /**
     * getAllTags
     * @return mixed[]
     */
    public function getAllTags(): array
    {
        return $this->swaptags;
    }
    public function getSwapTagString(string $tagname): string
    {
        if (array_key_exists($tagname, $this->swaptags) == false) {
            $this->swaptags[$tagname] = "";
        }
        if ($this->swaptags[$tagname] == null) {
            $this->swaptags[$tagname] = "";
        }
        return $this->swaptags[$tagname];
    }
    public function getSwapTagInt(string $tagname): ?int
    {
        if (array_key_exists($tagname, $this->swaptags) == false) {
            $this->swaptags[$tagname] = 0;
        }
        return intval($this->swaptags[$tagname]);
    }

    /**
     * getSwapTagArray
     * @return mixed[]
     */
    public function getSwapTagArray(string $tagname): ?array
    {
        if (array_key_exists($tagname, $this->swaptags) == false) {
            $this->swaptags[$tagname] = [];
        }
        return $this->swaptags[$tagname];
    }
    public function getSwapTagBool(string $tagname): ?bool
    {
        if (array_key_exists($tagname, $this->swaptags) == false) {
            $this->swaptags[$tagname] = null;
        }
        return $this->swaptags[$tagname];
    }
    public function addSwapTagString(string $tagname, string $add_me = null): ?string
    {
        $current = $this->getSwapTagString($tagname);
        $current .= $add_me;
        $this->swaptags[$tagname] = $current;
        return $current;
    }
    public function setSwapTag($tagname, $newvalue): void
    {
        $this->swaptags[$tagname] = $newvalue;
    }
    /**
     * setSwapTagArray
     * sets a swaptag as an array
     * good for ajax replys, knida pointless for everything
     * else
     * @return mixed[]
     */
    public function setSwapTagArray(string $tagname, array $newvalue): array
    {
        $this->swaptags[$tagname] = $newvalue;
        return $this->swaptags[$tagname];
    }
    public function pageTitle(string $newvalue = null): string
    {
        if ($newvalue != null) {
            $this->setSwapTag("PAGE_TITLE", $newvalue);
        }
        return $this->getSwapTagString("PAGE_TITLE");
    }
    /**
     * metaTags
     * Creates a new metatag
     * @return mixed[]
     */
    public function metaTags(string $add_tag = null): array
    {
        if (array_key_exists("META_TAGS", $this->swaptags) == false) {
            $this->swaptags["META_TAGS"] = [];
        }
        $this->swaptags["META_TAGS"][] = $add_tag;
        return $this->swaptags["META_TAGS"];
    }
    protected function keypairReplace(string $output, array $oldpairs): string
    {
        $keypairs = [];
        foreach ($oldpairs as $key => $value) {
            $keypairs["[[" . $key . "]]"] = $value;
        }
        return strtr($output, $keypairs);
    }
}
