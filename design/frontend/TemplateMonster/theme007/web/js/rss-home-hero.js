define([], function () {
    'use strict';

    return function (config, element) {
        var hero = element;
        var video = hero.querySelector('.rss-home-hero__video');
        var videoUrl = hero.getAttribute('data-rss-video');
        var isMobile = window.matchMedia('(max-width: 767px)').matches;
        var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var started = false;
        var completed = false;

        function getCookie(name) {
            var prefix = name + '=';
            var parts = document.cookie ? document.cookie.split(';') : [];
            var i;
            var item;

            for (i = 0; i < parts.length; i += 1) {
                item = parts[i].trim();
                if (item.indexOf(prefix) === 0) {
                    return decodeURIComponent(item.substring(prefix.length));
                }
            }

            return null;
        }

        function showPodiums() {
            if (completed) {
                return;
            }

            completed = true;
            hero.classList.remove('rss-home-hero--video-playing');
            hero.classList.add('rss-home-hero--video-complete');

            if (video) {
                window.setTimeout(function () {
                    video.pause();
                    video.removeAttribute('src');
                    video.load();
                }, 220);
            }
        }

        function startVideo() {
            var playPromise;

            if (started || completed) {
                return;
            }

            started = true;

            if (!video || !videoUrl || !isMobile || reduceMotion) {
                showPodiums();
                return;
            }

            video.muted = true;
            video.playsInline = true;
            video.addEventListener('playing', function () {
                hero.classList.add('rss-home-hero--video-playing');
            }, {once: true});
            video.addEventListener('ended', showPodiums, {once: true});
            video.addEventListener('error', showPodiums, {once: true});
            video.addEventListener('abort', showPodiums, {once: true});
            video.setAttribute('src', videoUrl);
            video.load();

            playPromise = video.play();

            if (playPromise && typeof playPromise.catch === 'function') {
                playPromise.catch(showPodiums);
            }
        }

        if (!video || !videoUrl || !isMobile || reduceMotion) {
            showPodiums();
            return;
        }

        if (document.getElementById('rss-cookie-consent') &&
            getCookie('cookienotice') === null) {
            window.addEventListener('rss:cookie-consent-resolved', startVideo, {once: true});
            return;
        }

        startVideo();
    };
});
