<?php

namespace Unimatrix\Utility\Auth;

use Cake\Event\Event;
use Cake\Core\Configure;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\Auth\BaseAuthenticate;
use Cake\Controller\Component\CookieComponent;

/**
 * Simple Auth
 * - Uses cookie to store login info if the cookie component is loaded
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
 * Cookie autologin (in controller)
 * ----------------------------------------------------------
 * public function beforeFilter(Event $event) {
 *     parent::beforeFilter($event);
 *
 *     if(!$this->Auth->user() && $this->Cookie->read('SimpleAuth')) {
 *         $user = $this->Auth->identify();
 *
 *         if($user) $this->Auth->setUser($user);
 *         else $this->Cookie->delete('SimpleAuth');
 *     }
 * }
 *
 * @author Flavius
 * @version 0.3
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

        // got cookie?
        if($this->cookieLoaded()) {
            $cookie = $this->_registry->Cookie->read('SimpleAuth');
            if($cookie)
                $request->data = $cookie;
        }

        // match against form data
        $valid = $request->data === $config;

        // save to cookie
        if($valid && $this->cookieLoaded()) {
            $this->_registry->Cookie->configKey('SimpleAuth', ['expires' => '+1 month']);
            $this->_registry->Cookie->write('SimpleAuth', $request->data);
        }

        // return output (without password)
        unset($config['password']);
        return $valid ? $config : false;
    }

    /**
     * Returns a list of all events that this authenticate class will listen to.
     *
     * @return array
     */
    public function implementedEvents() {
        return [
            'Auth.logout' => 'logout'
        ];
    }

    /**
     * Delete cookies when an user logout.
     *
     * @param \Cake\Event\Event  $event The logout Event.
     * @param array $user The user about to be logged out.
     *
     * @return void
     */
    public function logout(Event $event, array $user) {
        if($this->cookieLoaded())
            $this->_registry->Cookie->delete('SimpleAuth');
    }

    /**
     * Is the Cookie Component loaded?
     */
    private function cookieLoaded() {
        return isset($this->_registry->Cookie) && $this->_registry->Cookie instanceof CookieComponent;
    }
}