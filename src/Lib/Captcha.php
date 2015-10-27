<?php

namespace Borg\Lib;

/**
 * Captcha
 * Math captcha for forms
 *
 * Usage exmaple (in controller)
 * ----------------------------------------
 * use Borg\Lib\Captcha;
 *
 * // init captcha
 * $this->_captcha = new Captcha();
 * $session = $this->request->session();
 *
 * // use sessions and set into layout
 * $key = 'Captcha->' . $this->request->controller .'-'. $this->request->action;
 * $this->_captcha->set($session->read($key));
 * $session->write($key, $this->_captcha->get());
 * $this->set([$this->_captcha->field => (string)$this->_captcha]);
 *
 * // validate on post submit
 * if(!$this->_captcha->verify($this->request->data))
 *     $model->errors('captcha', ['Rezultatul matematic nu este corect']);
 *
 * @author Borg
 * @version 0.1
 */
class Captcha {
    public $field = null;

    private $_a = 0;
    private $_b = 0;
    private $_x = [];

    /**
     * Constructor
     * @param string $s - The name for the security input
     */
    public function __construct($s = 'captcha') {
        $this->_a = mt_rand(0, 9);
        $this->_b = mt_rand(1, 10);
        $this->field = $s;
    }

    /**
     * Set result from session
     * @param array $nr
     */
    public function set($nr = []) {
        $this->_x = $nr;
    }

    /**
     * Get the random numbers as array
     * @return array
     */
    public function get() {
        return [$this->_a, $this->_b];
    }

    /**
     * Check against the result from session
     *
     * @param array $m - The request model with reference
     * @return boolean
     */
    public function verify(&$m = []) {
        $val = isset($m[$this->field]) ? $m[$this->field] : 0;
        unset($m[$this->field]); // unset from request

        return intval($val) == array_sum($this->_x) ?: false;
    }

    /**
     * On returning this class as a string
     * @return string
     */
    public function __toString() {
        $f = function($i) {
            switch ($i) {
                case 0: return __('Zero'); case 1:  return __('Unu');   case 2: return __('Doi');
                case 3: return __('Trei'); case 4:  return __('Patru'); case 5: return __('Cinci');
                case 6: return __('Șase'); case 7:  return __('Șapte'); case 8: return __('Opt');
                case 9: return __('Nouă'); case 10: return __('Zece');
            }
        };

        return $f($this->_a) . ' plus ' . $f($this->_b);
    }
}