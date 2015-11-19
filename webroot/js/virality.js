/**
 * Virality Script
 *
 * @author Flavius
 * @version 0.1
 */
Frontend.virality = function() { 'use strict';
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
            url: '//connect.facebook.net/en_US/all.js',
            success: _fb_ready
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

    // init
    }, __construct = function(z, delay) {
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
    };

    // public, yay
    return {
        init: __construct
    }
}();

// social settings
window.___gcfg = {parsetags: 'explicit'};
