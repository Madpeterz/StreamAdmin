<?php

$this->output->addSwapTagString("html_title", " ~ Clear");
$this->output->addSwapTagString("page_title", " : Clear");
$this->output->setSwapTagString("page_actions", "");

$form = new form();
$form->target("objects/clear");
$form->required(true);
$form->col(6);
$form->group("Clear all objects (DB only)");
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
$this->output->setSwapTagString("page_content", $form->render("Clear", "warning"));
