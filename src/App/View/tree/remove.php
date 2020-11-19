<?php

$this->output->addSwapTagString("html_title", " ~ Remove");
$this->output->addSwapTagString("page_title", " Remove tree:" . $this->page);
$this->output->setSwapTagString("page_actions", "");
if (in_array($this->page, [6,10]) == false) {
    $form = new form();
    $form->target("tree/remove/" . $this->page . "");
    $form->required(true);
    $form->col(6);
    $form->group("Warning");
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
} else {
    $this->output->redirect("notice?bubblemessage=This notice is protected&bubbletype=warning");
}
