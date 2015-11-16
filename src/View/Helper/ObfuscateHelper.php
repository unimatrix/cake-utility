<?php

namespace Borg\View\Helper;

use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\View\Helper;
use Cake\View\View;
use Cake\Utility\Text;

/**
 * Obfuscate
 * Will obfuscate email addresses
 *
 * Load:
 * ---------------------------------------------------
 * This helper must be loaded in your View/AppView.php
 * before you can use it
 *
 * $this->loadHelper('Borg.Obfuscate');
 *
 * Usage:
 * ---------------------------------------------------
 * $this->Obfuscate->email('someone@something.com', [
 *     'text' => 'E-mail me right now!',
 *     'subject' => 'Your subject',
 *     'body' => 'Your body',
 *     'cc' => 'cc@something.com',
 *     'bcc' => 'bcc@something.com',
 * ]);
 *
 * @author Borg
 * @version 0.1
 */
class ObfuscateHelper extends Helper {
    // load html and url helpers
    public $helpers = ['Html'];

    // default conf
    protected $_defaultConfig = [];

    /**
     * Constructor
     * @param View $View
     * @param unknown $settings
     */
    public function __construct(View $View, array $config = []) {
        // call parent constructor
        parent::__construct($View, $config);
    }

    /**
     * Email obfuscator
     */
    public function email($address, $options = []) {
        // text
        $text = $address;
        if(isset($options['text'])) {
            $text = $options['text'];
            unset($options['text']);
        }

        // build query
        $query = http_build_query($options, false, '&', PHP_QUERY_RFC3986);
        if($query)
            $query = "?{$query}";

        // obfuscate
        $unique = Text::uuid();
        $obfuscated = str_rot13($this->Html->link($text, "mailto:{$address}{$query}"));

        // output
        $output = "<span id='{$unique}'>
            <script>
                document.getElementById('{$unique}').innerHTML = '{$obfuscated}'.replace(/[a-zA-Z]/g, function(c) {
                    return String.fromCharCode((c <= 'Z' ? 90 : 122) >= (c = c.charCodeAt(0) + 13) ? c : c - 26);
                });
            </script>
        </span>";

        // return output
        return $output;
     }
}