<?php

namespace App\Template;

use App\Template\Grid as Grid;

class Form
{
    protected $targeturl = "";
    protected $output = "";
    protected $add_required = false;
    protected $mode = "post";
    protected $mygrid = null;
    protected $allowAjax = true;
    protected function enablegridRender(): void
    {
        if ($this->mygrid == null) {
            $this->mygrid = new Grid();
        }
    }
    public function noAjax(): void
    {
        $this->allowAjax = false;
    }
    public function col(int $size = 12): void
    {
        $this->enableGridRender();
        $this->mygrid->col($size);
    }
    public function mode(string $newmode): void
    {
        $newmode = strtolower($newmode);
        if ($newmode == "get") {
            $this->mode = "get";
        } else {
            $this->mode = "post";
        }
    }
    public function split(): void
    {
        $this->enableGridRender();
        $this->mygrid->closeRow();
        $this->mygrid->addContent("<hr/>", 12);
        $this->mygrid->closeRow();
    }
    public function render(
        string $buttontext,
        string $buttonclass = "success",
        bool $slow_warning = false,
        bool $no_margin_top = false
    ): string {
        $this->enableGridRender();
        $this->mygrid->closeRow();
        $ajax_mode = "ajax";
        if ($slow_warning == true) {
            $ajax_mode = "ajax slow";
        }
        if ($this->allowAjax == false) {
            $ajax_mode = "";
        }
        if ($this->mode == "post") {
            $this->mygrid->addBefore('<form action="[[url_base]]' . $this->targeturl
            . '" method="POST" class="form ' . $ajax_mode . '">');
        } else {
            $this->mygrid->addBefore('<form action="[[url_base]]' . $this->targeturl . '" method="GET" class="form">');
        }
        $mgtop = "mt-4";
        if ($no_margin_top == true) {
            $mgtop = "";
        }
        $this->mygrid->addAfter('<div class="row ' . $mgtop . '"><div class="col-12"><button type="submit" '
        . 'class="btn btn-' . $buttonclass . '">' . $buttontext . '</button></div></div></form>');
        return $this->mygrid->getOutput();
    }
    public function required(bool $status): void
    {
        $add_required = $status;
    }
    protected function requiredAddon(): string
    {
        if ($this->add_required == true) {
            return "required";
        } else {
            return "";
        }
    }
    public function target(string $target): void
    {
        $this->enableGridRender();
        $this->targeturl = $target;
    }
    public function group($groupname): void
    {
        $this->enableGridRender();
        $this->mygrid->addContent('<h4>' . $groupname . '</h4>@NL@');
    }
    public function directAdd(string $content): void
    {
        $this->enableGridRender();
        $this->mygrid->addContent($content);
    }
    protected function startField(): void
    {
        $this->enableGridRender();
        $this->mygrid->addContent('<div class="input-group">@NL@');
    }
    protected function endField(): void
    {
        $this->enableGridRender();
        $this->mygrid->addContent('</div>@NL@');
    }
    protected function addLabel(string $label, string $name): void
    {
        $this->enableGridRender();
        $this->mygrid->addContent('<label for="' . $name . '" class="col-6 col-form-label">' . $label . '</label>@NL@');
    }
    public function textarea(string $name, string $label, int $max_length, ?string $value, string $placeholder): void
    {
        $this->enableGridRender();
        $this->addLabel($label, $name);
        $this->startField();
        $classUsed = "form-control";
        $addon = "";
        if (($max_length != 9999) &&  ($max_length > 0)) {
            $classUsed = "form-control inputwithlimit";
            $addon = 'data-lengthmax="' . $max_length . '"';
        }
        $this->mygrid->addContent('<textarea class="' . $classUsed . '" '
        . $addon . ' id="' . $name . '" name="' . $name . '"');
        $this->mygrid->addContent(' placeholder="' . $placeholder . '" ' . $this->requiredAddon() . '');
        $this->mygrid->addContent(' rows="5">' . $value . '</textarea>@NL@');
        $this->endField();
    }
    public function textInput(
        string $name,
        string $label,
        int $max_length,
        ?string $value,
        string $placeholder,
        string $mask = "",
        string $mode = "text",
        bool $readonly = false
    ): void {
        if ($mode != "hidden") {
            $this->enableGridRender();
            $this->addLabel($label, $name);
            $this->startField();
        }
        $classUsed = "form-control";
        $addon = "";
        if (($max_length != 9999) && ($max_length > 0) && ($readonly == false)) {
            $classUsed = "form-control inputwithlimit";
            $addon = 'data-lengthmax="' . $max_length . '"';
        }

        $this->mygrid->addContent('<input type="' . $mode . '" ' . $addon . ' class="'
        . $classUsed . '" name="' . $name . '" id="' . $name . '"');
        $this->mygrid->addContent(' value="' . $value . '" placeholder="'
        . $placeholder . '" ' . $this->requiredAddon() . '');
        if ($readonly == true) {
            $this->mygrid->addContent(' readonly');
        }
        $this->mygrid->addContent(' >@NL@');
        if ($mode != "hidden") {
            $this->endField();
        }
    }
    public function textureInput(
        string $name,
        string $label,
        int $max_length,
        ?string $value,
        string $placeholder,
        string $mask = "",
        string $mode = "text"
    ): void {
        if ($mode != "hidden") {
            $this->enableGridRender();
            $this->addLabel($label, $name);
            $this->startField();
        }
        $this->mygrid->addContent('<input data-lengthmin="' . $max_length . '" data-lengthmax="'
        . $max_length . '" type="'
        . $mode . '" class="form-control inputwithlimit" id="' . $name . '" name="' . $name . '"');
        $this->mygrid->addContent(' value="' . $value . '" placeholder="'
         . $placeholder . '" ' . $this->requiredAddon() . '');
        $this->mygrid->addContent(' > <a href="http://secondlife.com/app/image/' . $value . '/1" target="_blank">'
        . '<i class="fas fa-images"></i></a> @NL@');
        if ($mode != "hidden") {
            $this->endField();
        }
    }
    public function uuidInput(string $name, string $label, ?string $value, string $placeholder): void
    {
        $this->textInput($name, $label, 36, $value, $placeholder, "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx");
    }
    public function hiddenInput(string $name, ?string $value): void
    {
        $this->textInput($name, $name, strlen($value), $value, "", "", "hidden");
    }
    public function numberInput(string $name, string $label, ?int $value, int $max_length, string $placeholder): void
    {
        $this->textInput($name, $label, $max_length, "" . $value . "", $placeholder);
    }
    public function select(string $name, string $label, $value, $options): void
    {
        $this->enableGridRender();
        $this->addLabel($label, $name);
        $this->startField();
        $this->mygrid->addContent('<select class="form-control" name="' . $name . '">@NL@');
        foreach ($options as $optval => $opttext) {
            $selected = "";
            if ($optval == $value) {
                $selected = " SELECTED";
            }
            $this->mygrid->addContent("<option value='" . $optval . "' " . $selected . ">"
            . $opttext . "</option>@NL@");
        }
        $this->mygrid->addContent('</select>@NL@');
        $this->endField();
    }
}
