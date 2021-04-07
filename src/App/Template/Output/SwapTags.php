<?php

namespace App\Template\Output;

use App\R7\Model\Slconfig;

abstract class SwapTags
{
    protected ?Slconfig $slconfig = null;
    protected $swaptags = [];
    public function __construct(bool $with_defaults = false)
    {
        global $slconfig;
        $this->slconfig = &$slconfig;
        if ($with_defaults == true) {
            $this->swaptags = [
            "@NL@" => "\r\n",
            "PAGE_TITLE" => "",
            "SITE_NAME" => "",
            "url_base" => null,
            "META_TAGS" => ""];
        }
    }
    public function getSwapTagString(string $tagname): ?string
    {
        if (array_key_exists($tagname, $this->swaptags) == false) {
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
        $current = $this->getSwapTagString($tagname);
        if ($current != $newvalue) {
            if ($newvalue !== null) {
                $this->swaptags[$tagname] = $newvalue;
            }
        }
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
    public function urlBase(string $newvalue = null): string
    {
        if ($newvalue != null) {
            $this->setSwapTag("url_base", $newvalue);
        }
        return $this->getSwapTagString("url_base");
    }
    public function pageTitle(string $newvalue = null): string
    {
        if ($newvalue != null) {
            $this->setSwapTag("PAGE_TITLE", $newvalue);
        }
        return $this->getSwapTagString("PAGE_TITLE");
    }
    public function siteName(string $newvalue = null): string
    {
        if ($newvalue != null) {
            $this->setSwapTag("SITE_NAME", $newvalue);
        }
        return $this->getSwapTagString("SITE_NAME");
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
