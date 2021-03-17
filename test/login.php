<?php

require __DIR__ . '/../PhpFormBuilder.php';

$form = new FormBuilder();

//$form->add('Endereço de e-mail', "type:email, class:form-control, maxlength, required:1, autofocus:1");
$form->add('Endereço de e-mail', [
    'id' => 'email',
    'type' => 'email',
    'class' => 'form-control',
    'maxlength',
    'required' => true,
    'autofocus' => true
]);
$form->newLine();

$form->add('Password', ['type' => 'password', 'id' => 'password', 'class' => 'form-control']);
$form->newLine();

$form->render();
