<?php

$this->output->addSwapTagString("html_title", " ~ Remove");
$this->output->addSwapTagString("page_title", "Remove reseller:" . $this->page);
$this->output->setSwapTagString("page_actions", "");


$form = new form();
$form->target("reseller/remove/" . $this->page . "");
$form->required(true);
$form->col(6);
$form->group("Warning</h4><p>If the reseller is currenly in use this will fail<br/>You should just mark them as not allowed!</p><h4>");
$action = '
<div class="btn-group btn-group-toggle" data-toggle="buttons">
  <label class="btn btn-outline-danger active">
    <input type="radio" value="Accept" name="accept" autocomplete="off" > Accept
  </label>
  <label class="btn btn-outline-secondary">
    <input type="radio" value="Nevermind" name="accept" autocomplete="off" checked> Nevermind
  </label>
</div>';
$form->directAdd($action);
$this->output->setSwapTagString("page_content", $form->render("Remove", "danger"));
