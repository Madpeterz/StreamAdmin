<?php
class grid
{
    protected $col_value = 0;
    protected $row_open = false;
    protected $col_open = false;
    protected $output = "";
    public function get_output(bool $show_steps = false) : string
    {
        $this->close_col();
        $this->close_row();
        $sending = $this->output;
        $this->output = "";
        if($sending == null) return "";
        else return $sending;
    }
    public function add_before(string $content)
    {
        $this->output = $content."".$this->output;
    }
    public function add_after(string $content)
    {
        $this->output .= $content;
    }
    public function add_content(string $content,int $size=0)
    {
        if($size > 0)
        {
            $this->col($size);
        }
        $this->output .= $content;
    }
    public function col(int $size)
    {
        $this->close_col();
        if(($this->col_value+$size) > 12)
        {
            $this->close_row();
        }
        if($this->row_open == false)
        {
            $this->row();
        }
        $this->col_value += $size;
        $this->col_open = true;
        $this->output .= '<div class="col-'.$size.'">@NL@';
    }
    public function close_row()
    {
        $this->close_col();
        if($this->row_open == true)
        {
            $this->row_open = false;
            $this->col_value = 0;
            $this->output .= '</div>@NL@';
        }
    }
    protected function close_col()
    {
        if($this->col_open == true)
        {
            $this->col_open = false;
            $this->output .= '</div>@NL@';
        }
    }
    protected function row()
    {
        $this->close_row();
        $this->output .= '<div class="row">@NL@';
        $this->row_open = true;
    }

}
class form
{
    protected $targeturl = "";
    protected $output = "";
    protected $add_required = false;
    protected $mode = "post";
    protected $mygrid = null;
    protected function enable_grid_render()
    {
        if($this->mygrid == null)
        {
            $this->mygrid = new grid();
        }
    }
    public function col(int $size=12)
    {
        $this->enable_grid_render();
        $this->mygrid->col($size);
    }
    public function mode(string $newmode)
    {
        if($newmode == "get") $this->mode = "get";
        else $this->mode = "post";
    }
    public function split()
    {
        $this->enable_grid_render();
        $this->mygrid->close_row();
        $this->mygrid->add_content("<hr/>",12);
        $this->mygrid->close_row();
    }
    public function render(string $buttontext,string $buttonclass="success") : string
    {
        $this->enable_grid_render();
        $this->mygrid->close_row();
        if($this->mode == "post")
        {
            $this->mygrid->add_before('<form action="[[url_base]]ajax.php/'.$this->targeturl.'" method="POST" class="form ajax">');
        }
        else
        {
            $this->mygrid->add_before('<form action="[[url_base]]'.$this->targeturl.'" method="POST" class="form">');
        }
        $this->mygrid->add_after('<div class="row mt-4"><div class="col-12"><button type="submit" class="btn btn-'.$buttonclass.'">'.$buttontext.'</button></div></div></form>');
        return $this->mygrid->get_output();
    }
    public function required(bool $status)
    {
        $add_required = $status;
    }
    protected function required_addon() : string
    {
        if($this->add_required == true) return "required";
        else return "";
    }
    public function target(string $target)
    {
        $this->enable_grid_render();
        $this->targeturl = $target;
    }
    public function group($groupname)
    {
        $this->enable_grid_render();
        $this->mygrid->add_content('<h4>'.$groupname.'</h4>@NL@');
    }
    public function direct_add(string $content)
    {
        $this->enable_grid_render();
        $this->mygrid->add_content($content);
    }
    protected function start_field()
    {
        $this->enable_grid_render();
        $this->mygrid->add_content('<div class="input-group">@NL@');
    }
    protected function end_field()
    {
        $this->enable_grid_render();
        $this->mygrid->add_content('</div>@NL@');
    }
    protected function add_label(string $label,string $name)
    {
        $this->enable_grid_render();
        $this->mygrid->add_content('<label for="'.$name.'" class="col-6 col-form-label">'.$label.'</label>@NL@');
    }
    public function textarea(string $name,string $label,int $max_length,?string $value,string $placeholder)
    {
        $this->enable_grid_render();
        $this->add_label($label,$name);
        $this->start_field();
        $this->mygrid->add_content('<textarea class="form-control" name="'.$name.'"');
        $this->mygrid->add_content(' placeholder="'.$placeholder.'" '.$this->required_addon().'');
        $this->mygrid->add_content(' rows="21">'.$value.'</textarea>@NL@');
        $this->end_field();
    }
    public function text_input(string $name,string $label,int $max_length,?string $value,string $placeholder,string $mask="")
    {
        $this->enable_grid_render();
        $this->add_label($label,$name);
        $this->start_field();
        $this->mygrid->add_content('<input type="text" class="form-control" name="'.$name.'"');
        $this->mygrid->add_content(' value="'.$value.'" placeholder="'.$placeholder.'" '.$this->required_addon().'');
        $this->mygrid->add_content(' >@NL@');
        $this->end_field();
    }
    public function uuid_input(string $name,string $label,?string $value,string $placeholder)
    {
        $this->text_input($name,$label,36,$value,$placeholder,"xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx");
    }
    public function number_input(string $name,string $label,?int $value,int $max_length,string $placeholder)
    {
        $this->text_input($name,$label,$max_length,"".$value."",$placeholder);
    }
    public function select(string $name,string $label,$value,$options)
    {
        $this->enable_grid_render();
        $this->add_label($label,$name);
        $this->start_field();
        $this->mygrid->add_content('<select class="form-control" name="'.$name.'">@NL@');
        foreach($options as $optval => $opttext)
        {
            $selected = "";
            if($optval == $value)
            {
                $selected = " SELECTED";
            }
            $this->mygrid->add_content("<option value='".$optval."' ".$selected.">".$opttext."</option>@NL@");
        }
        $this->mygrid->add_content('</select>@NL@');
        $this->end_field();
    }
}
?>
