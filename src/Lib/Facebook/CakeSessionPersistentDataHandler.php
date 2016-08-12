<?php

namespace Unimatrix\Utility\Lib\Facebook;

/**
 * Cake Session Persistent Data Handler
 * Enables facebook-php-sdk to use the correct cake session objects
 *
 * Usage example:
 * ---------------------------------
 * // controller
 * use Unimatrix\Utility\Lib\Facebook\CakeSessionPersistentDataHandler;
 * use Facebook;
 *
 * $this->facebook = new Facebook\Facebook([
 *     'app_id' => $cfg['app'],
 *     'app_secret' => $cfg['secret'],
 *     'default_graph_version' => $cfg['version'],
 *     'persistent_data_handler' => new CakeSessionPersistentDataHandler($this->request->session()),
 * ]);
 *
 * @author Flavius
 * @version 0.1
 */
class CakeSessionPersistentDataHandler implements \Facebook\PersistentData\PersistentDataInterface
{
    private $session = false;
    public function __construct($session) {
        $this->session = $session;
    }

    public function get($key) {
        return $this->session->consume("Facebook.{$key}");
    }

    public function set($key, $value) {
        $this->session->write("Facebook.{$key}", $value);
    }
}