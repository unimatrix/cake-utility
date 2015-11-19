<?php

namespace Unimatrix\Utility\Controller\Component;

use Cake\Controller\Component;
use Cake\Routing\Router;
use Cake\Utility\Xml;
use Cake\Utility\Inflector;
use Cake\I18n\Time;

/**
 * Sitemap component
 * Basic sitemap implementation
 *
 * Example of routes configured for sitemap
 * --------------------------------------------------
 * $routes->connect('/', ['controller' => 'Index', 'action' => 'index'], ['sitemap' => ['modified' => time(), 'frequency' => 'daily', 'priority' => '1.0']]);
 * $routes->connect('/page', ['controller' => 'Page', 'action' => 'display'], ['sitemap' => ['modified' => time(), 'frequency' => 'monthly', 'priority' => '0.5']]);
 *
 * @author Flavius
 * @version 0.1
 */
class SitemapComponent extends Component
{
    // Load request handler
    public $components = ['RequestHandler'];

    // the exclusion array
    public $exclude = [];

    // url storage
    protected $_url = [];

    // sitemap root
    protected $_root = '<?xml version="1.0" encoding="UTF-8"?><?xml-stylesheet type="text/xsl" href="%s"?><urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>';

    /**
     * Render sitemap
     * @return string as xml
     */
    public function render() {
        // respond as xml
        $this->RequestHandler->respondAs('xml');

        // start xml
        $xml = Xml::build(sprintf($this->_root, $this->_path('Unimatrix/Utility.xsl/sitemap.xsl')));

        // get urls from router
        $this->_router();

        // append urls
        $this->_append($xml, Xml::fromArray(['urlset' => ['url' => $this->_url]]));

        // return xml
        return $xml->asXML();
    }

    /**
     * Get urls from Router
     */
    protected function _router() {
        // go through each defined route
        foreach(Router::routes() as $route) {
            // not supposed to be in sitemap? exclude
            if(!isset($route->options['sitemap']))
                continue;

            // dynamic? exclude
            if(strpos($route->template, '*') !== false)
                continue;

            // in exclude array?
            if(in_array($route->template, $this->exclude))
                continue;

            // add record
            $this->_url[] = [
                'loc' => Router::url($route->template, true),
                'lastmod' => (new Time(isset($route->options['sitemap']['modified']) ? $route->options['sitemap']['modified'] : time()))->timezone(date_default_timezone_get())->toAtomString(),
                'changefreq' => isset($route->options['sitemap']['frequency']) ? $route->options['sitemap']['frequency'] : 'monthly',
                'priority' => isset($route->options['sitemap']['priority']) ? $route->options['sitemap']['priority'] : '0.6'
            ];
        }
    }

    /**
     * Generate URL for given asset file
     * @param string $asset
     * @return string
     */
    protected function _path($asset) {
        list($plugin, $path) = pluginSplit($asset, false);

        $path = Inflector::underscore($plugin) . '/' . $path;
        return rtrim(Router::fullBaseUrl(), '/') . '/' . ltrim($path, '/');
    }

    /**
     * SimpleXML helper, append 2 objects together
     * @param SimpleXML $to
     * @param SimpleXML $from
     */
    protected function _append(&$to, $from) {
        // go through each kid
        foreach($from->children() as $child) {
            $temp = $to->addChild($child->getName(), (string) $child);
            foreach($child->attributes() as $key => $value)
                $temp->addAttribute($key, $value);

            // perform append
            $this->_append($temp, $child);
        }
    }
}
