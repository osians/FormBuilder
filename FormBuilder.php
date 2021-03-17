<?php

require_once __DIR__ . '/FormBuilderOutput.php';

/**
 * Class FormBuilder
 *
 * Based on https://github.com/joshcanhelp/php-form-builder code from JoshCanHelp
 * this was remade by wsantana to add more features and fix some issues
 *
 * @package FormBuilder
 * @since 2021.02.21 0126
 * @author wsantana <sans.pds@gmail.com>
 * @version 0.8.7
 */
class FormBuilder
{
    /**
     * @var array Stores all form inputs
     */
	private $inputs = array();

    /**
     * @var array Stores all form attributes
     */
	private $form = array();

	/**
	 * Constructor function to set form action and attributes
	 *
	 * @param string $action
	 * @param array  $attr
	 */
    function __construct($action = '', $attr = array())
    {
        // Default form attributes
        $defaults = array(
            'action' => $action,
            'method' => 'post',
            'enctype' => 'application/x-www-form-urlencoded',
            'class' => array(),
            'id' => '',
            'markup' => 'html',
            'novalidate' => false,
            'add_nonce' => false,
            'add_honeypot' => true,
            'form_element' => true,
            'add_submit' => true
        );

        $settings = (!empty($attr) && is_array($attr)) ? array_merge($defaults, $attr) : $defaults;

        foreach ($settings as $key => $val) {
            if (!$this->setFormAttr($key, $val)) {
                $this->setFormAttr($key, $defaults[$key]);
            }
        }
    }

    public function getForm()
    {
        return $this->form;
    }

    public function getInputs()
    {
        return $this->inputs;
    }

    /**
     * Validate and set form attribute
     *
     * @param string $key A valid key; switch statement ensures validity
     * @param string | bool $val A valid value; validated for each key
     *
     * @return bool
     */
    function setFormAttr($key, $val)
    {
        switch ($key) {
            case 'action':
                break;

            case 'method':
                if (!in_array($val, array('post', 'get'))) {
                    return false;
                }
                break;

            case 'enctype':
                if (!in_array($val, array('application/x-www-form-urlencoded', 'multipart/form-data'))) {
                    return false;
                }
                break;

            case 'markup':
                if (!in_array($val, array('html', 'xhtml'))) {
                    return false;
                }
                break;

            case 'class':
                if (!$this->isValidClassName($val)) {
                    return false;
                }
                break;
            case 'id':
                if (!$this->isValidId($val)) {
                    return false;
                }
                break;

            case 'novalidate':
            case 'add_honeypot':
            case 'form_element':
            case 'add_submit':
                if (!is_bool($val)) {
                    return false;
                }
                break;

            case 'add_nonce':
                if (!is_string($val) && !is_bool($val)) {
                    return false;
                }
                break;

            default:
                return false;
        }

        $this->form[$key] = $val;

        return true;
    }

    /**
     * Add an input field to the form for outputting later
     *
     * @param string $label label
     * @param array $props props
     * @return FormBuilder
     */
    public function add($label, $props = array())
    {
        if (empty($props)) {
            $props = array();
        }

        $identification = (isset($props['id']) || isset($props['name']))
             ? (isset($props['id']) ? $props['id'] : $props['name'])
             : $this->makeSlug($label);

        $defaults = array(
            'type' => 'text',
            'name' => isset($props['name']) ? $props['name'] : $identification,
            'id' => $identification,
            'label' => $label,
            'value' => '',
            'placeholder' => '',
            'class' => array(),
            'min' => '',
            'max' => '',
            'step' => '',
            'autofocus' => false,
            'checked' => false,
            'selected' => false,
            'required' => false,
            'add_label' => true,
            'options' => array(),
            'wrap_tag' => 'div',
            'wrap_class' => array('form_field_wrap'),
            'wrap_id' => '',
            'wrap_style' => '',
            'before_html' => '',
            'after_html' => '',
            'request_populate' => true
        );

        $props = array_merge($defaults, $props);
        $this->inputs[$identification] = $props;
        return $this;
    }

    /**
     * Add a break line into the form
     * @return $this
     */
    public function newLine()
    {
        $index = $this->generateRandomString();
        $this->inputs[$index] = array(
            'type' => 'newline',
            'id' => $index,
            'class' => 'form-newline',
            'before_html' => '',
            'after_html' => '');
        return $this;
    }

    /**
     * @param $legend
     * @return $this
     */
    public function openFieldset($legend)
    {
        $idx = $this->generateRandomString();
        $this->inputs[$idx] = array(
            'type' => 'fieldset',
            'title' => $legend,
            'id' => $idx,
            'class' => 'form-fieldset',
            'before_html' => '',
            'after_html' => '');
        return $this;
    }

    public function closeFieldset() {
        $idx = $this->generateRandomString();
        $this->inputs[$idx] = array(
            'type' => 'closefieldset',
            'before_html' => '',
            'after_html' => ''
        );
        return $this;
    }

    /**
     * Generate random text
     * @param int $length
     * @return false|string
     */
    protected function generateRandomString($length = 10)
    {
        return substr(str_shuffle(
            str_repeat(
                $x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
                ceil($length / strlen($x))
            )),
            1, $length);
    }

	/**
	 * Add multiple inputs to the input queue
	 *
	 * @param $arr
	 *
	 * @return $this
	 */
	function add_inputs($arr)
    {
		if (!is_array($arr)) {
			return $this;
		}

		foreach ($arr as $field) {
			$this->add($field[0], (isset($field[1]) ? $field[1] : ''));
		}

		return $this;
	}

    /**
     * Add AntSpan
     * @return $this
     */
    public function addHoneypot()
    {
        if ($this->form['add_honeypot']) {
            $this->add('Leave blank to submit', array(
                'name' => 'honeypot',
                'slug' => 'honeypot',
                'id' => 'form_honeypot',
                'wrap_tag' => 'div',
                'wrap_class' => array('form_field_wrap', 'hidden'),
                'wrap_id' => '',
                'wrap_style' => 'display: none',
                'request_populate' => false
            ));
        }

        return $this;
    }

    /**
     * Validates id and class attributes
     * @param $value
     * @return false|int
     */
    private function isValidClassName($value)
    {
        if (is_array($value) && empty($value)) {
            return false;
        }
        return preg_match('/^[a-z0-9.\-_]+$/i', $value);
    }

    /**
     * @param $value
     * @return false|int
     */
    private function isValidId($value)
    {
        return preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $value);
    }

    /**
     * Create a friendly name from a string
     * @param $string
     * @return string
     */
    public function makeSlug($string)
    {
        $result = str_replace('"', '', $string);
        $result = str_replace("'", '', $result);
        $result = str_replace('_', '-', $result);
        $result = preg_replace('~[\W\s]~', '-', $result);
        $result = strtolower($result);

        return $result;
    }

    /**
     * render Form
     *
     * @param bool $echo
     *
     * @return string
     */
    public function render($echo = true)
    {
        if ($echo) {
            (new FormBuilderOutput($this))->render();
        } return (new FormBuilderOutput($this))->render();
    }
}
