<?php

$this->output->addSwapTagString("html_title", " ~ Remove");
$this->output->addSwapTagString("page_title", " Remove server:" . $this->page);
$this->output->setSwapTagString("page_actions", "");

$form = new form();
$form->target("server/remove/" . $this->page . "");
$form->required(true);
$form->col(6);
$form->group("Warning</h4><p>If the server currenly in use this will fail</p><h4>");
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
