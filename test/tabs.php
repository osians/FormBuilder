<?php

    ini_set('display_errors', 1);
    error_reporting(E_ALL);

?><!DOCTYPE HTML>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>PHPFormBuilder test</title>
</head>

<body>


<?php

require_once(__DIR__ . '/../FormBuilder.php');

/*
Create a new instance Method 1
Pass in a URL to set the action
*/
$form = new FormBuilder();

//Create a new instance Method 2
//$form = new PhpFormBuilder('path/to/action', [
//    'method' => 'post',
//    'enctype' => 'multipart/form-data',
//    'markup' => 'html',
//    'class' => 'class_1',
//    'class' => 'class_2',
//    'id' => 'a_contact_form',
//    'novalidate' => true,
//    'add_honeypot' => true,
//    'add_nonce' => 'a_contact_form',
//    'form_element' => true,
//    'add_submit' => true,
//]);

/*
Form attributes are modified with the set_att function.
First argument is the setting
Second argument is the value
*/

$form->setFormAttr('method', 'post');
$form->setFormAttr('enctype', 'multipart/form-data');
$form->setFormAttr('markup', 'html');
$form->setFormAttr('class', 'class_1');
$form->setFormAttr('class', 'class_2');
$form->setFormAttr('id', 'a_contact_form');
$form->setFormAttr('novalidate', true);
$form->setFormAttr('add_honeypot', true);
$form->setFormAttr('add_nonce', 'a_contact_form');
$form->setFormAttr('form_element', true);
$form->setFormAttr('add_submit', true);


/*
Uss add_input to create form fields
First argument is the name
Second argument is an array of arguments for the field
Third argument is an alternative name field, if needed
*/

$form->openTab('Meu Formulario', true);

$form->add('Name', ['id' => 'contact_name', 'request_populate' => false, 'autofocus' => true])
     ->add('Surname', array('id' => 'contact_surname', 'request_populate' => false))
     ->add('Email', array('id' => 'contact_email', 'type' => 'email', 'class' => array('class_1', 'class_2', 'class_3')))
     ->newLine()
     ->add('Files', array('id' => 'files_here', 'type' => 'file'));

$form->newLine();

$form->openTab('Outro Fieldset');
$form->add('Should we call you?', array(
    'type' => 'checkbox',
    'value' => 1
))->newLine();

$form->add('True or false', array(
    'type' => 'radio',
    'checked' => false,
    'value' => 1
));

$form->add('Reason for contacting', array(
    'type' => 'checkbox',
    'options' => array(
        'say_hi' => 'Just saying hi!',
        'complain' => 'I have a bone to pick',
        'offer_gift' => 'I\'d like to give you something neat',
    )
));

$form->add('Bad Headline', array(
    'type' => 'radio',
    'options' => array(
        'say_hi_2' => 'Just saying hi! 2',
        'complain_2' => 'I have a bone to pick 2',
        'offer_gift_2' => 'I\'d like to give you something neat 2',
    )
));

$form->newLine();

$form->add('Reason for contact', array(
    'type' => 'select',
    'options' => array(
        '' => 'Select...',
        'say_hi' => 'Just saying hi!',
        'complain' => 'I have a bone to pick',
        'offer_gift' => 'I\'d like to give you something neat',
    )
));
$form->newLine();
$form->add('Question or comment', array(
    'required' => true,
    'type' => 'textarea',
    'value' => 'Type away!'
));
$form->newLine();

$form->add_inputs(array(
    array('Field 1'),
    array('Field 2'),
    array('Field 3')
));

$form->newLine();

/*
Create the form
*/
$form->render();

/*
 * Debugging
 */
echo '<pre>';
print_r($_REQUEST);
echo '</pre>';
echo '<pre>';
print_r($_FILES);
echo '</pre>';
?>
</body>
</html>