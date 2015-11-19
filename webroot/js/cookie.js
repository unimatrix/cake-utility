/*!
 * JavaScript Cookie v2.0.3
 * https://github.com/js-cookie/js-cookie
 *
 * Copyright 2006, 2015 Klaus Hartl & Fagner Brack
 * Released under the MIT license
 */
(function (factory) {
	if (typeof define === 'function' && define.amd) {
		define(factory);
	} else if (typeof exports === 'object') {
		module.exports = factory();
	} else {
		var _OldCookies = window.Cookies;
		var api = window.Cookies = factory(window.jQuery);
		api.noConflict = function () {
			window.Cookies = _OldCookies;
			return api;
		};
	}
}(function () {
	function extend () {
		var i = 0;
		var result = {};
		for (; i < arguments.length; i++) {
			var attributes = arguments[ i ];
			for (var key in attributes) {
				result[key] = attributes[key];
			}
		}
		return result;
	}

	function init (converter) {
		function api (key, value, attributes) {
			var result;

			// Write

			if (arguments.length > 1) {
				attributes = extend({
					path: '/'
				}, api.defaults, attributes);

				if (typeof attributes.expires === 'number') {
					var expires = new Date();
					expires.setMilliseconds(expires.getMilliseconds() + attributes.expires * 864e+5);
					attributes.expires = expires;
				}

				try {
					result = JSON.stringify(value);
					if (/^[\{\[]/.test(result)) {
						value = result;
					}
				} catch (e) {}

				value = encodeURIComponent(String(value));
				value = value.replace(/%(23|24|26|2B|3A|3C|3E|3D|2F|3F|40|5B|5D|5E|60|7B|7D|7C)/g, decodeURIComponent);

				key = encodeURIComponent(String(key));
				key = key.replace(/%(23|24|26|2B|5E|60|7C)/g, decodeURIComponent);
				key = key.replace(/[\(\)]/g, escape);

				return (document.cookie = [
					key, '=', value,
					attributes.expires && '; expires=' + attributes.expires.toUTCString(), // use expires attribute, max-age is not supported by IE
					attributes.path    && '; path=' + attributes.path,
					attributes.domain  && '; domain=' + attributes.domain,
					attributes.secure ? '; secure' : ''
				].join(''));
			}

			// Read

			if (!key) {
				result = {};
			}

			// To prevent the for loop in the first place assign an empty array
			// in case there are no cookies at all. Also prevents odd result when
			// calling "get()"
			var cookies = document.cookie ? document.cookie.split('; ') : [];
			var rdecode = /(%[0-9A-Z]{2})+/g;
			var i = 0;

			for (; i < cookies.length; i++) {
				var parts = cookies[i].split('=');
				var name = parts[0].replace(rdecode, decodeURIComponent);
				var cookie = parts.slice(1).join('=');

				if (cookie.charAt(0) === '"') {
					cookie = cookie.slice(1, -1);
				}

				try {
					cookie = converter && converter(cookie, name) || cookie.replace(rdecode, decodeURIComponent);

					if (this.json) {
						try {
							cookie = JSON.parse(cookie);
						} catch (e) {}
					}

					if (key === name) {
						result = cookie;
						break;
					}

					if (!key) {
						result[name] = cookie;
					}
				} catch (e) {}
			}

			return result;
		}

		api.get = api.set = api;
		api.getJSON = function () {
			return api.apply({
				json: true
			}, [].slice.call(arguments));
		};
		api.defaults = {};

		api.remove = function (key, attributes) {
			api(key, '', extend(attributes, {
				expires: -1
			}));
		};

		api.withConverter = init;

		return api;
	}

	return init();
}));

/**
 * Cookie Policy
 * Will create the notice html for your cookie policy
 *
 * @author Flavius
 * @version 0.2
 */
Cookies.policy = function() { 'use strict';
	var store = {
		sticky: false,
		message: 'Acest site folosește cookies pentru a-ți oferi o experiență cât mai plăcută. Continuarea navigării implică acceptarea lor.',
		accept: 'Sunt de acord',
		page: '/cookies',
		details: 'Mai multe detalii',
		parent: $('body')

	// html5 sticky footer
	}, _html5 = function() {
		// do html
		var html = '<div class="cookie-policy">' +
	        '<div>' +
	            '<span>' + store.message + '</span>' +
	            '<div>' +
	            	'<a href="javascript:void(0);">' + store.accept + '</a>' +
	            	'<a href="' + store.page + '" target="_blank" rel="nofollow">' + store.details + '</a>' +
	            '</div>' +
	        '</div>' +
	    '</div>';

		// append to parent
		store.parent.append(html);

		// define policy and spacer
		var policy = $('div.cookie-policy', store.parent);

		// setup the spacer
		$('body').css({marginBottom: parseInt($('body').css('marginBottom').replace('px', '')) + policy.outerHeight()});
		$('footer').css({marginBottom: parseInt($('footer').css('marginBottom').replace('px', '')) + policy.outerHeight()});

		// on i agree click
		$('div.cookie-policy > div > div > a:first-child', store.parent).on('click', function() {
			// hide element
            policy.fadeOut('fast', function() {
        		$('body').removeAttr('style');
        		$('footer').removeAttr('style');
            });

			// set cookie
			Cookies.set('cookie_accept', '1', {
				expires: 30,
				path: '/'
			});
		});

	// build
	}, _build = function() {
		// do html
		var html = '<div class="cookie-policy">' +
	        '<div>' +
	            '<span>' + store.message + '</span>' +
	            '<div>' +
	            	'<a href="javascript:void(0);">' + store.accept + '</a>' +
	            	'<a href="' + store.page + '" target="_blank" rel="nofollow">' + store.details + '</a>' +
	            '</div>' +
	        '</div>' +
	    '</div>' +
	    '<div class="cookie-policy-spacer"></div>';

		// append to parent
		store.parent.append(html);

		// define policy and spacer
		var policy = $('div.cookie-policy', store.parent),
			spacer = $('div.cookie-policy-spacer', store.parent);

		// setup the spacer
		spacer.height(policy.outerHeight());

		// on i agree click
		$('div.cookie-policy > div > div > a:first-child', store.parent).on('click', function() {
			// hide element
            policy.fadeOut('fast', function() {
            	spacer.animate({height: 0}, 'fast');
            });

			// set cookie
			Cookies.set('cookie_accept', '1', {
				expires: 30,
				path: '/'
			});
		});

	// init
	}, __construct = function(cfg) {
		// set configuration
		store = $.extend({}, store, cfg);

		// build cookies
		if(Cookies.get('cookie_accept') !== '1')
			store.sticky ? _html5() : _build();
	};

	// public, yay
	return {
	    init: __construct
	};
}();
