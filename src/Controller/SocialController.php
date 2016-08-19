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
 * @version 0.2
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
    // agent
    private $agent = [
        'method' => "GET",
        'header' => "Accept-language: en\r\n" .
        "User-Agent: Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.102011-10-16 20:23:10\r\n"
    ];

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
        $contents = file_get_contents('https://www.facebook.com/plugins/like.php?href=' . urlencode($url) . '&width=50&layout=box_count&action=like&size=small&show_faces=false&share=true&height=65', false, stream_context_create(['http' => $this->agent]));
        preg_match('/\<span class\=\"pluginCountTextDisconnected\"\>([\d]+)\<\/span\>/', $contents, $matches);

        // return counter
        return isset($matches[1]) ? (int)$matches[1] : 0;
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
        $contents = file_get_contents('https://plusone.google.com/_/+1/fastbutton?url=' . urlencode($url), false, stream_context_create(['http' => $this->agent]));
        preg_match('/window\.__SSR = {c: ([0-9]*\.[0-9]+|[0-9]+) \,a/', $contents, $matches);

        // return counter
        return isset($matches[1]) ? (int)$matches[1] : 0;
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