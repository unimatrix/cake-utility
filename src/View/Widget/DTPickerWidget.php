<?php

namespace Unimatrix\Utility\View\Widget;

use Cake\View\Widget\WidgetInterface;
use Cake\View\Form\ContextInterface;
use Cake\I18n\FrozenTime;
use Cake\I18n\FrozenDate;
use Cake\Utility\Text;

/**
 * DTPicker
 * This widget is used in conjunction with the dtpicker jquery plugin
 * @see https://curioussolutions.github.io/DateTimePicker/
 *
 * Example:
 * ---------------------------------------------------------------------------------
 * echo $this->Form->input('date1', ['type' => 'dtpicker']); // mode is `datetime` by default
 * echo $this->Form->input('date2', ['type' => 'dtpicker', 'mode' => 'date']);
 * echo $this->Form->input('date3', ['type' => 'dtpicker', 'mode' => 'time']);
 *
 * @author Flavius
 * @version 0.3
 */
class DTPickerWidget implements WidgetInterface
{
    /**
     * StringTemplate instance.
     *
     * @var \Cake\View\StringTemplate
     */
    protected $_templates;

    /**
     * Constructor.
     *
     * @param \Cake\View\StringTemplate $templates Templates list.
     */
    public function __construct($templates) {
        $this->_templates = $templates;
    }

    /**
     * Render a text widget or other simple widget like email/tel/number.
     *
     * This method accepts a number of keys:
     *
     * - `name` The name attribute.
     * - `val` The value attribute.
     * - `escape` Set to false to disable escaping on all attributes.
     *
     * Any other keys provided in $data will be converted into HTML attributes.
     *
     * @param array $data The data to build an input with.
     * @param \Cake\View\Form\ContextInterface $context The current form context.
     * @return string
     */
    public function render(array $data, ContextInterface $context) {
        $data += [
            'name' => '',
            'val' => null,
            'type' => 'text',
            'mode' => 'datetime',
            'escape' => true,
            'readonly' => true,
            'templateVars' => []
        ];

        // mode value and class
        $mode = $data['mode'];
        $hval = $data['value'] = $data['val'];
        $data['class'] = $data['type'];
        unset($data['val'], $data['mode']);

        // transform into frozen time if not already
        if(!($data['value'] instanceof FrozenTime || $data['value'] instanceof FrozenDate))
            $data['value'] = new FrozenTime($data['value']);

        // transform values
        if($mode == 'datetime') {
            $hval = $data['value']->format('Y-m-d H:i:s');
            $data['value'] = $data['value']->format('d-M-Y H:i:s');
        }
        if($mode == 'date') {
            $hval = $data['value']->format('Y-m-d');
            $data['value'] = $data['value']->format('d-M-Y');
        }
        if($mode == 'time')
            $hval = $data['value'] = $data['value']->format('H:i:s');

        // add field type
        $data['data-field'] = $mode;

        // render
        $rand = Text::uuid();
        return "<div id='{$rand}' style='position: relative;'>" . $this->_templates->format('input', [
            'name' => $data['name'],
            'type' => 'hidden',
            'attrs' => $this->_templates->formatAttributes(['value' => $hval]),
        ]) . $this->_templates->format('input', [
            'name' => $data['name'] . '-' . $rand,
            'type' => 'text',
            'templateVars' => $data['templateVars'],
            'attrs' => $this->_templates->formatAttributes(
                $data,
                ['name', 'type']
            ),
        ]) . "<div id='dtpicker-{$rand}'></div><scriptend>$(document).ready(Backend.DTPicker('{$rand}', '{$mode}'));</scriptend></div>";
    }

    /**
     * {@inheritDoc}
     */
    public function secureFields(array $data) {
        if (!isset($data['name']) || $data['name'] === '')
            return [];

        return [$data['name']];
    }
}