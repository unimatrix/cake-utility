<?php

namespace Unimatrix\Utility\Auth;

use Cake\Core\Configure;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\Auth\BaseAuthenticate;

/**
 * Simple Auth
 *
 * Basic Config example:
 * ------------------------------------
 * 'SimpleAuth' => [
 *     'username' => 'admin',
 *     'password' => 'humanresources'
 * ],
 *
 * Basic Usage example:
* ----------------------------------------------------------
 * $this->loadComponent('Auth', [
 *     'authenticate' => ['Unimatrix/Utility.Simple'],
 *     'loginAction' => [
 *         'controller' => 'Admin',
 *         'action' => 'login'
 *     ]
 * ]);
 *
 * Different backend / frontend login config example:
 * ----------------------------------------------------------
 * 'SimpleAuth' => [
 *     'backend' => [
 *         'username' => 'admin',
 *         'password' => 'humanresources'
 *     ]
 * ],
 *
 * Different backend / frontend login usage example:
 * ----------------------------------------------------------
 * $this->loadComponent('Auth', [
 *     'authenticate' => ['Unimatrix/Utility.Simple' => ['type' => 'backend']],
 *     'loginAction' => [
 *         'controller' => 'Admin',
 *         'action' => 'login'
 *     ]
 * ]);
 *
 * @author Flavius
 * @version 0.2
 */
class SimpleAuthenticate extends BaseAuthenticate
{
    /**
     * Authenticate a user based on the request information.
     *
     * @param \Cake\Network\Request $request Request to get authentication information from.
     * @param \Cake\Network\Response $response A response object that can have headers added.
     * @return mixed Either false on failure, or an array of user data on success.
     */
    public function authenticate(Request $request, Response $response) {
        // get config
        $config = Configure::read('SimpleAuth' . (!is_null($this->config('type')) ? '.' . $this->config('type') : null));
        if(!$config || empty($config) || !isset($config['username']) || !isset($config['password']))
            return false;

        // match against form data
        $valid = $request->data === $config;
        unset($config['password']);

        // return output
        return $valid ? $config : false;
    }
}