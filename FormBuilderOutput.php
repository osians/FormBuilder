<?php

/**
 * Class FormBuilderOutput
 *
 * @package FormBuilder
 * @since 2021.02.21 0126
 * @author wsantana <sans.pds@gmail.com>
 * @version 0.8.7
 */
class FormBuilderOutput
{
    protected $formBuilder = null;

    /**
     * @var bool Does this form have a submit value?
     */
	private $hasSubmit = false;

    /**
     * FormBuilderOutput constructor.
     *
     * @param FormBuilder $formBuilder
     */
    public function __construct(FormBuilder $formBuilder)
    {
        $this->formBuilder = $formBuilder;
    }

    /**
     * Parses and builds the classes in multiple places
     *
     * @param array|string $classes
     *
     * @return string
     */
    private function parseClasses($classes)
    {
        if (is_array($classes) && count($classes) > 0) {
            $classes = implode(' ', $classes);
        }
        return (is_string($classes)) ? " class='{$classes}'" : '';
    }

    /**
     * Parse initial Form Tag
     *
     * @return string
     */
    protected function openFormTag()
    {
        $form = $this->formBuilder->getForm();

        if (!$form['form_element']) {
            return '';
        }

        $html = "<form method='{$form['method']}'";

        if (!empty($form['enctype'])) {
            $html .= " enctype='{$form['enctype']}'";
        }

        if (!empty($form['action'])) {
            $html .= " action='{$form['action']}'";
        }

        if (!empty($form['id'])) {
            $html .= " id='{$form['id']}'";
        }

        $hasClasses = is_string($form['class']) ? strlen($form['class']) > 0 : count($form['class']) > 0;
        if ($hasClasses) {
            $html .= $this->parseClasses($form['class']);
        }

        if ($form['novalidate']) {
            $html .= ' novalidate';
        }

        return "{$html}>";
    }

    /**
     * Easy way to auto-close fields, if necessary
     *
     * @return string
     */
	private function field_close()
    {
        $form = $this->formBuilder->getForm();
		return  $form['markup'] === 'xhtml' ? ' />' : '>';
	}

    /**
     * @param $input
     * @param $field
     *
     * @return string
     */
    protected function wrap($input, $field)
    {
        $output = '';

        if ($input['type'] != 'hidden' && $input['type'] != 'html') {

            $wrap_before = $input['before_html'];
            if (!empty($input['wrap_tag'])) {
                $wrap_before .= '<' . $input['wrap_tag'];
                $wrap_before .= count($input['wrap_class']) > 0 ? $this->parseClasses($input['wrap_class']) : '';
                $wrap_before .= !empty($input['wrap_style']) ? ' style="' . $input['wrap_style'] . '"' : '';
                $wrap_before .= !empty($input['wrap_id']) ? ' id="' . $input['wrap_id'] . '"' : '';
                $wrap_before .= '>';
            }

            $wrap_after = $input['after_html'];
            if (!empty($input['wrap_tag'])) {
                $wrap_after = '</' . $input['wrap_tag'] . '>' . $wrap_after;
            }

            $output .= $wrap_before . $field . $wrap_after;
        } else {
            $output .= $field;
        }

        return $output;
    }

    /**
     * @param $input
     * @return string
     */
    protected function getSpecialHtmlFields($input)
    {
        $attr = '';

        // Special HTML5 fields, if set
        $attr .= isset($input['autofocus']) && $input['autofocus'] != false ? ' autofocus' : '';
        $attr .= isset($input['checked']) && $input['checked'] != false ? ' checked' : '';
        $attr .= isset($input['required']) && $input['required'] != false ? ' required' : '';

        return $attr;
    }

    /**
     * @param $input
     * @return string
     */
    protected function getLabel($input)
    {
        $field = '';
        $label_html = '';

        if (($input['type'] == 'radio' || $input['type'] == 'checkbox') && count($input['options']) > 0) {
            $label_html = '<div class="checkbox_header">' . $input['label'] . '</div>';
        }

        if (!empty($label_html)) {
            $field .= $label_html;
        } elseif (isset($input['add_label']) && !in_array($input['type'], array('hidden', 'submit', 'title', 'html'))) {
            if ($input['required']) {
                $input['label'] .= ' <strong>*</strong>';
            }
            $field .= '<label for="' . $input['id'] . '">' . $input['label'] . '</label>';
        }

        return $field;
    }

    /**
     * @param $input
     * @return string
     */
    protected function parseNewline($input)
    {
        $id = !empty($input['id']) ? ' id="' . $input['id'] . '"' : '';
        $class = $this->parseClasses($input['class']);

        return "<div {$id} {$class}></div>";
    }

    /**
     * @param $input
     * @return string
     */
    protected function parseFieldset($input)
    {
        $id = !empty($input['id']) ? ' id="' . $input['id'] . '"' : '';
        $class = $this->parseClasses($input['class']);
        return "<fieldset {$class} {$id}><legend>{$input['title']}</legend>";
    }

    /**
     * @param $input
     * @return string
     */
    protected function parseClosefieldset()
    {
        return '</fieldset>';
    }

    /**
     * @param $input
     * @return string
     */
    protected function parseTab($input)
    {
        $id = !empty($input['id']) ? ' id="' . $input['id'] . '"' : '';
        $defaultOpen =  ($input['open']) ? 'style="display:block;"' : '';
        return "<div class='tabcontent' {$id} {$defaultOpen}>";
    }

    /**
     * @return string
     */
    protected function parseClosetab()
    {
        return '</div><!-- tab -->';
    }

    /**
     * @param $input
     * @return mixed
     */
    protected function parseHtml($input)
    {
        return $input['label'];
    }

    /**
     * @param $input
     * @return string
     */
    protected function parseTitle($input)
    {
        return "<h3>{$input['label']}</h3>";
    }

    /**
     * @param $input
     * @return string
     */
    protected function parseTextarea($input)
    {
        $id = !empty($input['id']) ? ' id="' . $input['id'] . '"' : '';
        $class = $this->parseClasses($input['class']);
        $attr = $this->getSpecialHtmlFields($input);

        $label = $this->getLabel($input);
        $field = "{$label}<textarea {$id} {$class} {$attr}>{$input['value']}</textarea>";
        return $this->wrap($input, $field);
    }

    /**
     * @param $input
     * @return string
     */
    protected function parseSelect($input)
    {
        $id = !empty($input['id']) ? ' id="' . $input['id'] . '"' : '';
        $class = $this->parseClasses($input['class']);
        $attr = $this->getSpecialHtmlFields($input);

        $options = '';
        foreach ($input['options'] as $key => $opt) {
            $isSelected = '';
            if (
                // Is this field set to automatically populate?
                $input['request_populate'] &&

                // Do we have $_REQUEST data to use?
                isset($_REQUEST[$input['name']]) &&

                // Are we currently outputting the selected value?
                $_REQUEST[$input['name']] === $key
            ) {
                $isSelected = ' selected';

                // Does the field have a default selected value?
            } else if ($input['selected'] === $key) {
                $isSelected = ' selected';
            }
            $options .= "<option value='{$key} {$isSelected}>{$opt}</option>";
        }

        $label = $this->getLabel($input);
        $field = "{$label}<select {$id} name='{$input['name']}' {$class} {$attr}>{$options}</select>";
        return $this->wrap($input, $field);

    }

    /**
     * @param $input
     * @return string
     */
    protected function parseRadioAndCheckbox($input)
    {
        $field = '';

        if (count($input['options']) <= 0) {
            return $field;
        }

        // Special case for multiple check boxes
        $field = '<div class="checkbox_header">' . $input['label'] . '</div>';
        foreach ($input['options'] as $key => $opt) {
            $slug = $this->formBuilder->makeSlug($opt);
            $field .= "<div class='checkbox_wrapper'>";
            $field .= sprintf(
                '<input type="%s" name="%s[]" value="%s" id="%s"',
                $input['type'],
                $input['name'],
                $key,
                $slug
            );

            if (
                // Is this field set to automatically populate?
                $input['request_populate'] &&
                // Do we have $_REQUEST data to use?
                isset($_REQUEST[$input['name']]) &&
                // Is the selected item(s) in the $_REQUEST data?
                in_array($key, $_REQUEST[$input['name']])
            ) {
                $field .= ' checked';
            }

            $field .= $this->field_close();
            $field .= " <label for='{$slug}'>{$opt}</label>";
            $field .= '</div>';
        }

        return $this->wrap($input, $field);
    }

    /**
     * @param $input
     * @return string
     */
    protected function parseRadio($input) {
        return $this->parseRadioAndCheckbox($input);
    }

    /**
     * @param $input
     * @return string
     */
    protected function parseCheckbox($input) {
        return $this->parseRadioAndCheckbox($input);
    }

    protected function parseText($input)
    {
        $id = !empty($input['id']) ? ' id="' . $input['id'] . '"' : '';
        $class = $this->parseClasses($input['class']);
        $attr = $this->getSpecialHtmlFields($input);
        $min_max_range = '';

        // Special number values for range and number types
        if ($input['type'] === 'range' || $input['type'] === 'number') {
            $min_max_range .= !empty($input['min']) ? ' min="' . $input['min'] . '"' : '';
            $min_max_range .= !empty($input['max']) ? ' max="' . $input['max'] . '"' : '';
            $min_max_range .= !empty($input['step']) ? ' step="' . $input['step'] . '"' : '';
        }

        $body = ' type="' . $input['type'] . '" value="' . $input['value'] . '"';
        $body .= $input['checked'] ? ' checked' : '';

        $label = $this->getLabel($input);
        $field = "{$label}<input {$id} name='{$input['name']}' {$min_max_range} {$class} {$attr} {$body} {$this->field_close()}";
        return $this->wrap($input, $field);
    }

    /**
     * @param $input
     * @return string
     */
    protected function parseRange($input) {
        return $this->parseText($input);
    }

    /**
     * @param $input
     * @return string
     */
    protected function parseNumber($input) {
        return $this->parseText($input);
    }

    /**
     * @param $input
     * @return string
     */
    protected function parseEmail($input) {
        return $this->parseText($input);
    }

    /**
     * @param $input
     * @return string
     */
    protected function parseFile($input) {
        return $this->parseText($input);
    }

    /**
     * Automatic population for field on submit
     * @param $input
     */
    protected function autoPopulateField(&$input)
    {
        // Automatic population of values using $_REQUEST data
        if (isset($input['request_populate']) && isset($_REQUEST[$input['name']])) {
            // Can this field be populated directly?
            if (!in_array($input['type'], array('html', 'title', 'radio', 'checkbox', 'select', 'submit'))) {
                $input['value'] = $_REQUEST[$input['name']];
            }
        }

        // Automatic population for checkboxes and radios
        if (
            isset($input['request_populate']) &&
            ($input['type'] == 'radio' || $input['type'] == 'checkbox') &&
            empty($input['options'])
        ) {
            $input['checked'] = isset($_REQUEST[$input['name']]) ? true : $input['checked'];
        }
    }

    /**
     * @return string
     */
    protected function renderTabs()
    {
        $tabs = array();
        foreach ($this->formBuilder->getInputs() as $input) {
            if ($input['type'] === 'tab') {
                $tabs[] = $input;
            }
        }

        if (empty($tabs)) {
            return '';
        }

        $html = '<div class="tab">';
        foreach ($tabs as $tab) {
            $selectecTab = ($tab['open']) ? 'active' : '';
            $html .= "<button class='tablinks {$tab['class']} {$selectecTab}' onclick='openFormTab(event, \"{$tab['id']}\");'>{$tab['title']}</button>";
        }

        return "{$html}</div>";

//  <button class="tablinks" onclick="openCity(event, 'Paris')">Paris</button>
//  <button class="tablinks" onclick="openCity(event, 'Tokyo')">Tokyo</button>
//</div>
    }

	/**
	 * Build the HTML for the form based on the input queue
	 *
	 * @param bool $echo Should the HTML be echoed or returned?
	 *
	 * @return string
	 */
    public function render($echo = true)
    {
        // default style, to make life easy
        $output  = "<link rel='stylesheet' href='/FormBuilder.css'>";
        $output .= "<script src='/FormBuilder.js'></script>";

        $output .= $this->renderTabs();

        $output .= $this->openFormTag();
        $this->formBuilder->addHoneypot();

        // Iterate through the input queue and add input HTML
        foreach ($this->formBuilder->getInputs() as $input) {

            // Automatic population of values using $_REQUEST data
            $this->autoPopulateField($input);

            if ($input['type'] === 'submit') {
                $this->hasSubmit = true;
                continue;
            }

            $method = "parse" . ucfirst(strtolower($input['type']));
            $output .= $this->$method($input);
        }

        // Auto-add submit button
        $form = $this->formBuilder->getForm();
        if (!$this->hasSubmit && $form['add_submit']) {
            $output .= '<div class="form_field_wrap"><input type="submit" value="Submit" name="submit"></div>';
        }

        // Close the form tag if one was added
        if ($form['form_element']) {
            $output .= '</form>';
        }

        if ($echo) {
            echo $output; exit(0);
        } return $output;
    }
}