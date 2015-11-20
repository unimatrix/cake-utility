<?php

namespace Unimatrix\Utility\Lib;

/**
 * Helpers
 * Contains helper functions
 *
 * @author Flavius
 * @version 0.1
 */
class Misc {
    /**
     * Unimatrix dump
     *
     * To have access to this function directly just by writing 'dump(...)' in your code,
     * load your unimatrix\utility plugin like this in bootstrap or use directly via Misc::dump(...)
     * ------------------------------------------------------------------------------------
     * // Load unimatrix\utility plugin @ bootstrap
     * use Unimatrix\Utility\Lib\Misc;
     * Plugin::load('Unimatrix/Utility', ['routes' => true]);
     * function dump($a, $b = null, $c = false) {
     *     return Misc::dump($a, $b, $c);
     * }
     *
     * @param unknown $var
     * @param string $title
     * @param bool $return
     */
    public static function dump($var, $title = null, $return = false) {
        // start
        $output = null;

        // start output
        $output .= "<pre style='font-family: monospace; white-space: pre; margin: 1em; clear: both; background: #F5F5F5; border: 1px solid brown; padding: 10px; position: relative; z-index: 9999; font-size: 13px; line-height: 16px; text-align: left; text-shadow: none; color: #000;'>";
        if(!is_null($title))
            $output .= "<span style='color: brown; font-family: Verdana; font-size: 16px; display: block; padding-bottom: 2px; margin-bottom: 10px; border-bottom: 1px solid brown;'>{$title}</span>";

        // finish output
		ob_start();
		var_dump($var);
        $output .= ob_get_clean() . "</pre>";

        // return or echo
        if($return) return $output;
        else echo $output;
    }
}