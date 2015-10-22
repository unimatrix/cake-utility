/**
 * Frontend Script
 *
 * @author Borg
 * @version 0.5
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
        dump('| 7 of 8, Web Drone of Unimatrix 7384 |');
        dump("~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~");

        // preload images
        Frontend.helpers.preload(typeof Preload === 'undefined' ? [] : Preload);

        // load analytics
        if(!DEV_ENV)
            _analytics();
    };

    // public, yay
    return {
        init: __construct,
        mobile: mobile,
        minicart: minicart,
        ajax: ajax,
        load: load
    };
}();

// frontend helpers
Frontend.helpers = function() { 'use strict';
    var store = {

    // image preloader
    }, preload = function(x) {
        $(x).each(function () {
            $('<img />').attr('src', this).appendTo('section.preload');
        });
    };

    // public, yay
    return {
        preload: preload
    }
}();

// Frontend social
Frontend.social = function() { 'use strict';
    var store = {
        fb_app_id: $('meta[property="fb:app_id"]').attr('content'),
        container: undefined,
        fb_done: false,
        gl_done: false

    // load facebook sdk
    }, _fb_load = function() {
        // already loaded?
        if(store.fb_done)
            return _fb_ready();

        // use ajax
        $.ajax({
            cache: true,
            dataType: 'script',
            url: '//connect.facebook.net/en_US/all.js'
//          , success: _fb_ready // not needed because of window.fbAsyncInit (best practice)
        });

    // load google platform
    }, _gl_load = function() {
        // already loaded?
        if(store.gl_done)
            return _gl_ready();

        // use ajax
        $.ajax({
            cache: true,
            dataType: 'script',
            url: '//apis.google.com/js/platform.js',
            success: _gl_ready
        });

    // on facebook ready
    }, _fb_ready = function () {
        // app id not found?
        if(typeof store.fb_app_id == 'undefined')
            return alert('could not find facebook app id');

        // reset counter
        $('#facebook_count', store.container).html('~');

        // render
        $('div.facebook', store.container).html('<fb:like href="'+ store.container.data('href') +'" layout="button" action="like" show_faces="false" share="true"></fb:like>');

        // init fb or just re-render
        if(!store.fb_done) window.FB.init({ appId: store.fb_app_id, channelURL: WEBROOT + 'channel.php', status: false, cookie: false, oauth: false, xfbml: true });
        else window.FB.XFBML.parse($('div.facebook', store.container).get(0));

        // mark as done
        store.fb_done = true;

    // on google ready
    }, _gl_ready = function() {
        // mark as done
        store.gl_done = true;

        // reset counter
        $('#google_count', store.container).html('~');

        // render
        $('div.google', store.container).html('<g:plusone href="'+ store.container.data('href') +'" size="medium" annotation="none"></g:plusone>');
        window.gapi.plusone.go($('div.google', store.container).get(0));

    // do social counter
    }, virality = function(z, delay) {
        // no container
        if(!z.length > 0)
            return;

        // set container
        store.container = z;

        // render facebook and google
        var render = function() {
            _fb_load();
            _gl_load();
        };

        // with delay or without
        if(delay) window.setTimeout(render, delay);
        else render();

        // show counters
        var show = function() {
            // got mask? make it vanish
            if($('div.mask', store.container).length > 0)
                $('div.mask', store.container).fadeOut(200);

            // setup counters
            Frontend.ajax({type: 'POST', data: {urls: store.container.data('encoded')}, dataType: 'json', url: 'borg/social/counter', success: function(x) {
                _count($('#facebook_count', store.container), 0, x.f, 500);
                _count($('#google_count', store.container), 0, x.g, 500);
            }});
        }

        // with delay or without
        if(delay) window.setTimeout(show, delay + 1000);
        else window.setTimeout(show, 200);

    // 1 by 1 counting animation
    }, _count = function(ele, start, end, duration) {
        var range = end - start,
            minTimer = 50,
            stepTime = Math.abs(Math.floor(duration / range));

        // never go below minTimer
        stepTime = Math.max(stepTime, minTimer);

        // get current time and calculate desired end time
        var startTime = new Date().getTime(),
            endTime = startTime + duration,
            timer;

        // format counter
        var out = function(x) {
            if (x >= 1e6) x = (x / 1e6).toFixed(2) + "M";
            else if (x >= 1e3) x = (x / 1e3).toFixed(1) + "k";

            return x;
        };

        // magic function
        var run = function() {
            var now = new Date().getTime();
            var remaining = Math.max((endTime - now) / duration, 0);
            var value = Math.round(end - (remaining * range));

            ele.html(out(value));
            if (value == end)
                window.clearInterval(timer);
        };

        // start
        timer = window.setInterval(run, stepTime);
        run();

    // window open
    }, _open = function(url) {
        // setup window
        var windowWidth = 500,
            windowHeight = 520,
            windowLeft = parseInt((screen.availWidth / 2) - (windowWidth / 2)),
            windowTop = parseInt((screen.availHeight / 2) - (windowHeight / 2)),
            windowSize = "width=" + windowWidth + ",height=" + windowHeight + ",left=" + windowLeft + ",top=" + windowTop + ",screenX=" + windowLeft + ",screenY=" + windowTop,
            windowName = "social",
            newwindow = window.open(url, windowName, windowSize);

        // do focus
        if(newwindow.focus)
            newwindow.focus();

    // init
    }, __construct = function() {
        // bind events
        $('div.social.facebook').on('click', function () { _open(WEBROOT + "social/facebook"); });
        $('div.social.google').on('click', function () { _open(WEBROOT + "social/google"); });
    };

    // public, yay
    return {
        init: __construct,
        fb_ready: _fb_ready,
        virality: virality
    }
}();

// social callbacks & settings
window.fbAsyncInit = Frontend.social.fb_ready;
window.___gcfg = {parsetags: 'explicit'};

// init frontend on ready
$(document).ready(Frontend.init);