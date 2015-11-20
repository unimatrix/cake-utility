<?php

namespace Unimatrix\Utility\Controller;

use Unimatrix\Utility\Controller\AppController;
use Cake\Network\Exception\NotFoundException;

/**
 * Social controller
 * Counter for social posts
 *
 * Usage:
 * -----------------------------------
 * Perform an ajax request to this controller (default route is yoursite/unimatrix/utility/social/counter)
 * The post must contain the key urls which has to be an serialized array that is then base64 encoded
 * containing one or more url to check
 *
 * @author Flavius
 * @version 0.1
 */
class SocialController extends AppController
{
    public function initialize() {
        parent::initialize();

        // request handler for json detection
        $this->loadComponent('RequestHandler');
    }

    /**
     * Get total likes shares and plus ones
     * @throws NotFoundException
     */
    public function counter() {
        // not ajax?
        if(!$this->request->is('ajax'))
            throw new NotFoundException();

        // not post
        if(!$this->request->is('post'))
            throw new NotFoundException();

        // no urls?
        if(is_null($this->request->data('urls')))
            throw new NotFoundException();

        // start social
        $social = new Social();

        // default
        $facebook_count = 0;
        $google_count = 0;

        // process data
        foreach(array_unique(unserialize(base64_decode($this->request->data('urls')))) as $url) {
            if(strpos($url, env('HTTP_HOST')) !== false) { // same domain protection
                $facebook_count += $social->facebookCount($url);
                $google_count += $social->googleCount($url);
            }
        }

        // output
        $response = [
            'success' => true,
            'data' => [
                'f' => $facebook_count,
                'g' => $google_count
            ]
        ];

        // send to template
        $this->set(compact('response'));
        $this->set('_serialize', ['response']);
    }
}

/**
 * Social network counter
 *
 * @author Flavius
 * @version 0.1
 */
class Social {
    /**
     * URL helper
     * @param string $url
     * @return boolean|string
     */
    private function _url($url = null) {
        if(is_null($url) || !strlen($url) > 0)
            return false;

        return $url;
    }

    /**
     * Get the facebook share and like count
     *
     * @param string $url
     * @return number
     */
    public function facebookCount($url = null) {
        // check for url
        if(!$this->_url($url))
            return false;

        // get counter
        $fql  = "SELECT share_count, like_count FROM link_stat WHERE url = '{$url}'";
        $fqlURL = "https://api.facebook.com/method/fql.query?format=json&query=" . urlencode($fql);
        $json = json_decode(file_get_contents($fqlURL));

        // return counter
        return (int)$json[0]->like_count + $json[0]->share_count;
    }

    /**
     * Get the google + count
     *
     * @param string $url
     * @return number
     */
    public function googleCount($url = null) {
        // check for url
        if(!$this->_url($url))
            return false;

        // get counter
        $contents = file_get_contents('https://plusone.google.com/_/+1/fastbutton?url=' . urlencode($url));
        preg_match('/window\.__SSR = {c: ([\d]+)/', $contents, $matches);

        // return counter
        return isset($matches[0]) ? (int)str_replace('window.__SSR = {c: ', '', $matches[0]) : 0;
    }

    /**
     * Shorter numbers
     *
     * @param number $count
     * @return string
     */
    public function _($count = 0) {
        if(($count / 1000000) > 1) $o = round($count / 1000000, 1) . 'm';
        else if(($count / 1000) > 1) $o = round($count / 1000, 1) . 'k';
        else $o = $count;

        return $o;
    }
}