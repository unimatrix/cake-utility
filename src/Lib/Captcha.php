<?php

namespace Unimatrix\Utility\Lib;

/**
 * Captcha
 * Math captcha for forms
 *
 * Usage exmaple (in controller)
 * ----------------------------------------
 * use Unimatrix\Utility\Lib\Captcha;
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
 * @author Flavius
 * @version 0.2
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
                case 0: return __d('captcha', 'Zero'); case 1:  return __d('captcha', 'Unu');   case 2: return __d('captcha', 'Doi');
                case 3: return __d('captcha', 'Trei'); case 4:  return __d('captcha', 'Patru'); case 5: return __d('captcha', 'Cinci');
                case 6: return __d('captcha', 'Șase'); case 7:  return __d('captcha', 'Șapte'); case 8: return __d('captcha', 'Opt');
                case 9: return __d('captcha', 'Nouă'); case 10: return __d('captcha', 'Zece');
            }
        };

        return $f($this->_a) . ' ' . __d('captcha', 'plus') . ' ' . $f($this->_b);
    }
}