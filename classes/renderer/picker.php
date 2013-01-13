<?php

namespace Sdrdis\Datetime;

class Renderer_Picker extends \Fieldset_Field
{
    public $template = '{label}{required} {field}{enabled} {date} {time}';

    protected static $DEFAULT_RENDERER_OPTIONS = array(
        'datepicker' => array(
            'showOn' => 'both',
            'buttonImage' => 'static/novius-os/admin/novius-os/img/icons/date-picker.png',
            'buttonImageOnly' => true,
            'autoSize' => true,
            'dateFormat' => 'yy-mm-dd',
            'showButtonPanel' => true,
            'changeMonth' => true,
            'changeYear' => true,
            'showOtherMonths' => true,
            'selectOtherMonths' => true,
            'gotoCurrent' => true,
            'firstDay' => 1,
            'showAnim' => 'slideDown',
        ),
        'timepicker' => array(
            'timeFormat' => 'hh:mm',
            'separator' => ' ',
        ),
        'wrapper' => '', //'<div class="datepicker-wrapper"></div>',
    );

    /**
     * Standalone build of the media renderer.
     *
     * @param  array  $renderer Renderer definition (attributes + renderer_options)
     * @return string The <input> tag + JavaScript to initialise it
     */
    public static function renderer($renderer = array())
    {
        list($attributes, $renderer_options) = static::parse_options($renderer);

        return '<input '.array_to_attr($attributes).' />'.static::js_init($attributes['id'], $renderer_options);
    }

    protected $options = array();

    public function __construct($name, $label = '', array $renderer = array(), array $rules = array(), \Fuel\Core\Fieldset $fieldset = null)
    {
        list($attributes, $this->options) = static::parse_options($renderer);

        if (!isset($this->options['default'])) {
            $this->options['default'] = \Date::forge()->format('%Y-%m-%d %H:%M:%S');
        }

        parent::__construct($name, $label, $attributes, $rules, $fieldset);
    }

    /**
     * How to display the field
     * @return type
     */
    public function build()
    {
        parent::build();
        $this->apply_field();
        $this->apply_enabled();
        $this->apply_date();
        $this->apply_time();
        $this->fieldset()->append(static::js_init($this->get_attribute('id'), $this->options));
        $datepicker_options = $this->options['datepicker'];
        $this->set_attribute('data-datepicker-options', htmlspecialchars(\Format::forge()->to_json($datepicker_options)));

        $datetime = $this->value;

        if (!($this->value && $this->value !='0000-00-00 00:00:00')) {
            $datetime = $this->options['default'];
        }

        if ($this->value == '0000-00-00 00:00:00') {
            $this->value = $datetime;
        }

        $date = \Date::create_from_string($datetime, 'mysql');

        $this->set_attribute('data-date', htmlspecialchars($date->format('%Y-%m-%d'))); //@todo: how to sync with dateFormat ?
        $this->set_attribute('data-time', htmlspecialchars($date->format('%H:%M'))); //@todo: how to sync with timeFormat ?

        return (string) parent::build();
    }

    public function apply_field() {
        $is_on_template = strpos($this->template, '{field}');
        if ($is_on_template === false) {
            $this->template = $this->template.'{field}';
        }
    }

    public function apply_enabled()
    {
        $time = \View::forge('sdrdis_datetime::renderer/enabled', array(
            'id' => $this->get_attribute('id'),
        ), false);
        $this->template = str_replace('{enabled}', $time, $this->template);
    }

    public function apply_time()
    {
        $time = \View::forge('sdrdis_datetime::renderer/time', array(
            'id' => $this->get_attribute('id'),
            'timepicker_options' => $this->options['timepicker'],
        ), false);
        $this->template = str_replace('{time}', $time, $this->template);
    }

    public function apply_date()
    {
        $date = \View::forge('sdrdis_datetime::renderer/date', array(
            'id' => $this->get_attribute('id'),
            'datepicker_options' => $this->options['datepicker'],
        ), false);
        $this->template = str_replace('{date}', $date, $this->template);
    }

    /**
     * Parse the renderer array to get attributes and the renderer options
     * @param  array $renderer
     * @return array 0: attributes, 1: renderer options
     */
    protected static function parse_options($renderer = array())
    {
        $renderer['type'] = 'text';
        $renderer['style'] = 'display: none;';

        // Default options of the renderer
        $renderer_options = static::$DEFAULT_RENDERER_OPTIONS;

        if (!empty($renderer['renderer_options'])) {
            $renderer_options = \Arr::merge($renderer_options, $renderer['renderer_options']);
        }
        unset($renderer['renderer_options']);

        return array($renderer, $renderer_options);
    }

    /**
     * Generates the JavaScript to initialise the renderer
     *
     * @param   string  HTML ID attribute of the <input> tag
     * @return string JavaScript to execute to initialise the renderer
     */
    protected static function js_init($id, $renderer_options = array())
    {

        return \View::forge('sdrdis_datetime::renderer/picker', array(
            'id' => $id,
            'wrapper' => \Arr::get($renderer_options, 'wrapper', ''),
        ), false);
    }

    public function before_save($item, $data)
    {
        $item->{$this->name} = empty($data[$this->name]) ? null : $data[$this->name];
    }

}
