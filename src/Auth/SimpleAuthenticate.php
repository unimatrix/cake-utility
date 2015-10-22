<?php

namespace Borg\Auth;

use Cake\Core\Configure;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\Auth\BaseAuthenticate;

class SimpleAuthenticate extends BaseAuthenticate
{
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