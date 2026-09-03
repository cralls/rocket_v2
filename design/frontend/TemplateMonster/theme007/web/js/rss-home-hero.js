define([], function () {
    'use strict';

    return function (config, element) {
        var hero = element;
        var video = hero.querySelector('.rss-home-hero__video');
        var videoUrl = hero.getAttribute('data-rss-video');
        var isMobile = window.matchMedia('(max-width: 767px)').matches;
        var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var completed = false;

        function showPodiums() {
            if (completed) {
                return;
            }

            completed = true;
            hero.classList.add('rss-home-hero--video-complete');

            if (video) {
                window.setTimeout(function () {
                    video.pause();
                    video.removeAttribute('src');
                    video.load();
                }, 220);
            }
        }

        if (!video || !videoUrl || !isMobile || reduceMotion) {
            showPodiums();
            return;
        }

        video.muted = true;
        video.playsInline = true;
        video.addEventListener('ended', showPodiums, {once: true});
        video.addEventListener('error', showPodiums, {once: true});
        video.addEventListener('abort', showPodiums, {once: true});
        video.setAttribute('src', videoUrl);
        video.load();

        var playPromise = video.play();

        if (playPromise && typeof playPromise.catch === 'function') {
            playPromise.catch(showPodiums);
        }
    };
});
