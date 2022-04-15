<?php

namespace YAPF\Bootstrap\Template;

use YAPF\Framework\Helpers\FunctionHelper;

class Card
{
    protected bool $hasImage = false;
    protected string $imagePath = "";
    protected bool $hasTitle = false;
    protected string $title = "";
    protected bool $hasLink = false;
    protected string $linkAdd = "";
    protected bool $hasContent = false;
    protected string $content = "";
    protected bool $disableImageDomain = false;
    protected bool $allowNotFoundImageSwap = true;
    protected bool $useLazyLoadImage = false;
    protected string $lazyLoadImage = "";

    protected bool $limitImageHeight = false;
    protected int $maxImageHeight = 100000;

    protected string $imageClassText = "";

    public function imageHeight(int $max): Card
    {
        $this->limitImageHeight = true;
        $this->maxImageHeight = $max;
        return $this;
    }

    public function imageClass(string $class): Card
    {
        $this->imageClassText = $class;
        return $this;
    }

    public function imageNoAttachDomain(): Card
    {
        $this->disableImageDomain = true;
        return $this;
    }

    public function setTitle(string $title): Card
    {
        $this->hasTitle = true;
        $this->title = $title;
        return $this;
    }

    public function setImage(string $image): Card
    {
        $this->hasImage = true;
        $this->imagePath = $image;
        return $this;
    }

    public function setLink(string $link): Card
    {
        $this->hasLink = true;
        $this->linkAdd = $link;
        return $this;
    }

    public function setContent(string $content): Card
    {
        $this->hasContent = true;
        $this->content = $content;
        return $this;
    }

    public function disableOnErrorImage(): Card
    {
        $this->allowNotFoundImageSwap = false;
        return $this;
    }

    public function lazyLoad(string $holderURL): Card
    {
        $this->useLazyLoadImage = true;
        $this->lazyLoadImage = $holderURL;
        return $this;
    }

    public function render(): string
    {
        $link_start = "";
        $link_end = "";
        $link_url = $this->linkAdd;
        if ($this->hasLink == true) {
            $offsite = true;
            if (FunctionHelper::strContains($this->linkAdd, "http") == false) {
                $link_url = "[[SITE_URL]]" . $link_url;
                $offsite = false;
            }
            $link_start = '<a target="_BLANK" rel="noreferrer" href="' . $link_url . '">';
            if ($offsite == false) {
                $link_start = '<a href="' . $link_url . '">';
            }
            $link_end = "</a>";
        }
        $output = '<div class="card">';
        if ($this->hasImage == true) {
            $imageUrl = $this->imagePath;
            $styleAddon = "";
            if (FunctionHelper::strContains($imageUrl, "http") == false) {
                if ($this->disableImageDomain == false) {
                    $imageUrl = "[[SITE_URL]]" . $imageUrl;
                }
            }
            if ($this->limitImageHeight == true) {
                $styleAddon = ' style="max-height: ' . $this->maxImageHeight . 'px" ';
            }
            $output .= $link_start;
            $output .= '<img ';
            if ($this->allowNotFoundImageSwap == true) {
                $output .= 'onerror="this.src=\'[[SITE_URL]]images/nopreview.png\'"';
            }
            if ($this->useLazyLoadImage == true) {
                $output .= ' data-load="' . $imageUrl . '"';
                $imageUrl = $this->lazyLoadImage;
                $this->imageClassText .= " el-image-swap";
            }
            $output .= ' src="';
            $output .= $imageUrl . '" class="card-img-top ';
            $output .= $this->imageClassText . '"';
            $output .= ' alt="Title image" ' . $styleAddon . '>';
            $output .= $link_end;
        }
        $output .= '<div class="card-body">';
        if ($this->hasTitle == true) {
            $output .= $link_start . '<h5 class="card-title">' . $this->title . '</h5>' . $link_end;
        }
        if ($this->hasContent == true) {
            $output .= '<p class="card-text mb-3">' . $this->content . '</p>';
        }
        $output .= '</div></div>';
        return $output;
    }
}
