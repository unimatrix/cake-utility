<?php

namespace Unimatrix\Utility\Auth;

use Cake\Core\Configure;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\Auth\BaseAuthenticate;

/**
 * Simple Auth
 *
 * Config example:
 * ------------------------------------
 * 'SimpleAuth' => [
 *     'username' => 'admin',
 *     'password' => 'humanresources'
 * ],
 *
 * Usage example:
 * ------------------------------------
 * $this->loadComponent('Auth', [
 *     'authenticate' => ['Unimatrix/Utility.Simple'],
 *     'loginAction' => [
 *         'controller' => 'Admin',
 *         'action' => 'login'
 *     ]
 * ]);
 *
 * @author Flavius
 * @version 0.1
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
        $config = Configure::read('SimpleAuth');
        if(!$config || empty($config) || !isset($config['username']) || !isset($config['password']))
            return false;

        // match against form data
        $valid = $request->data === $config;
        unset($config['password']);

        // return output
        return $valid ? $config : false;
    }
}