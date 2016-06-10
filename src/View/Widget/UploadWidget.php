<?php

namespace Unimatrix\Utility\View\Widget;

use Cake\View\Widget\WidgetInterface;
use Cake\View\Form\ContextInterface;
use Cake\View\View;

/**
 * Upload
 * This widget is used in conjunction with the uploadable behavior
 *
 * Configuration:
 * -------------------------------------------------------
 * read the configuration in the uploadable behaviour
 *
 * @author Flavius
 * @version 0.1
 */
class UploadWidget implements WidgetInterface
{
    /**
     * Constructor
     * @param \Cake\View\StringTemplate $templates Templates list.
     */
    public function __construct($templates) {
        $this->_templates = $templates;
    }

    /**
     * Render a file upload form widget.
     * @param array $data The data to build a file input with.
     * @param \Cake\View\Form\ContextInterface $context The current form context.
     * @return string HTML elements.
     */
    public function render(array $data, ContextInterface $context) {
        // default options
        $data += [
            'name' => '',
            'suffix' => '_upload',
            'escape' => true,
            'templateVars' => [],
        ];

        // overwrite name with behavior suffix
        $data['name'] = $data['name'] . $data['suffix'];
        unset($data['suffix']);

        // preview value
        $preview = null;
        if($data['val']) {
            $view = new View();
            $data['val'] = '/' . $data['val'];
            $preview = "<div class='preview'>{$view->Html->link($view->Html->image($data['val']), $data['val'], ['target' => '_blank', 'escape' => false])}</div>";
            unset($data['val']);
        }

        // return the actual template for this input type
        return $preview . $this->_templates->format('file', [
            'name' => $data['name'],
            'templateVars' => $data['templateVars'],
            'attrs' => $this->_templates->formatAttributes(
                $data,
                ['name', 'val']
            )
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function secureFields(array $data) {
        $fields = [];
        foreach(['name', 'type', 'tmp_name', 'error', 'size'] as $suffix)
            $fields[] = $data['name'] . '[' . $suffix . ']';

        return $fields;
    }
}