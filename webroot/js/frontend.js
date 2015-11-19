/**
 * Frontend Script
 *
 * @author Borg
 * @version 0.6
 */
var dump = function(what) { 'use strict';
    if(typeof console != 'undefined')
        console.log(what);
};

// not defined? assume it
if(typeof WEBROOT == 'undefined') WEBROOT = '/';
if(typeof DEV_ENV == 'undefined') DEV_ENV = true;

// start frontend
var Frontend = function() { 'use strict';
    var store = {
        js: {}

    // overwrite ajax settings
    }, ajax = function(o) {
        var overwrite = {
            url: (o.fullurl ? o.fullurl : (WEBROOT + o.url)),
            beforeSend: _loading(),

            // on success (w/ error handler)
            success: function(x) {
                if(x.response.success) return o.success ? o.success(x.response.data) : {};
                else return o.error ? o.error(x) : _error(x);

            // on error (custom or general)
            }, error: function(a, b, c) {
                return o.error ? o.error(a, b, c) : _error(a, b, c);
        }};

        // ajaxish
        $.ajax($.extend({}, o, overwrite)).always(_finished);

    // on ajax error, loading and finished
    }, _error = function() { console.error('Frontend.ajax request failed');
    }, _loading = function() { console.warn('Frontend.ajax request started');
    }, _finished = function() { console.info('Frontend.ajax request finished');

    // load javascript dynamically
    }, load = function(js, f) {
        if(store.js[js] == true) {
            if(f) f();
            return true;
        }

        // external?
        if(/^(http|https)\:\/\//i.test(js)) {
            $('body').append('<script type="text/javascript" src="'+ js +'" />');
            return store.js[js] = true;

        // not external?
        } else {
            // load the local script via ajax
            $.ajax({url: WEBROOT + 'js/'+ js, cache: true, dataType: "script", success: function() {
                store.js[js] = true;
                if(f) f();
            }});
        }

    // is mobile?
    }, mobile = function() {
    	try{ document.createEvent("TouchEvent"); return true; }
    	catch(e){ return false; }

	// minicart load
	}, minicart = function(a, p) {
		$.ajax({type: 'get', url: WEBROOT +'magazin/smallcart/', success: function(data) {
			// nothing in cart?
			var data = data.match(/window\.cartQty \= \'([a-z0-9\-]+)\'\;/i), count = data ? data[1] : 0;
			if(count == 0)
				return false;

			// change link
			a.attr('href', a.attr('href') + '/checkout/cart/');
			a.html('Coşul meu');

			// add counter
			p.css({position: 'relative'});
			p.append('<span class="noty">' + count + '</span>');

			// animate counter
			var n = p.find('span');
			n.fadeIn(250, function() {
				n.fadeOut(250, function() {
					n.fadeIn(250, function() {
						n.fadeOut(250, function(){
							n.fadeIn(250);
						})
					})
				})
			});
		}});

    // image preloader
    }, _preload = function(x) {
        $(x).each(function () {
            $('<img />').attr('src', this).appendTo('section.preload');
        });

    // load analytics
    }, _analytics = function() {
    	// google
    	(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
    	m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    	})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

        // facebook
    	!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
		n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
		n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
		t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
		document,'script','//connect.facebook.net/en_US/fbevents.js');

    // init
    }, __construct = function() {
        dump("~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~");
        dump('| Unimatrix Venture Digital Platform. |');
        dump("~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~");

        // preload images
        _preload(typeof Preload === 'undefined' ? [] : Preload);

        // load analytics
        if(!DEV_ENV)
            _analytics();
    };

    // public, yay
    return {
        init: __construct,
        ajax: ajax,
        load: load,
        mobile: mobile,
        minicart: minicart
    };
}();

// init frontend on ready
$(document).ready(Frontend.init);
