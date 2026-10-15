/* Mind OS landing — header state, mobile nav, service tabs, scroll reveal. */
(function () {
    'use strict';

    document.documentElement.classList.add('js');

    var header = document.querySelector('.site-header');
    var onScroll = function () {
        if (header) header.classList.toggle('is-scrolled', window.scrollY > 24);
    };
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });

    // Mobile navigation
    var toggle = document.querySelector('.nav-toggle');
    var nav = document.getElementById('site-nav');
    if (toggle && nav) {
        var close = function () {
            nav.classList.remove('is-open');
            toggle.setAttribute('aria-expanded', 'false');
        };
        toggle.addEventListener('click', function () {
            var open = nav.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            if (open && header) header.classList.add('is-scrolled');
        });
        nav.addEventListener('click', function (e) {
            if (e.target.closest('a')) close();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') close();
        });
        window.addEventListener('resize', function () {
            if (window.innerWidth > 1080) close();
        });
    }

    // Service tabs (accessible tablist)
    document.querySelectorAll('[role="tablist"]').forEach(function (list) {
        var tabs = Array.prototype.slice.call(list.querySelectorAll('[role="tab"]'));
        var select = function (tab) {
            tabs.forEach(function (t) {
                var on = t === tab;
                t.setAttribute('aria-selected', on ? 'true' : 'false');
                t.tabIndex = on ? 0 : -1;
                var panel = document.getElementById(t.getAttribute('aria-controls'));
                if (panel) panel.hidden = !on;
            });
        };
        tabs.forEach(function (tab, i) {
            tab.addEventListener('click', function () { select(tab); });
            tab.addEventListener('keydown', function (e) {
                var d = e.key === 'ArrowRight' ? 1 : e.key === 'ArrowLeft' ? -1 : 0;
                if (!d) return;
                var next = tabs[(i + d + tabs.length) % tabs.length];
                next.focus();
                select(next);
            });
        });
    });

    // Reveal on scroll
    var items = document.querySelectorAll('.reveal');
    if (!('IntersectionObserver' in window)) {
        items.forEach(function (el) { el.classList.add('is-visible'); });
        return;
    }
    var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                io.unobserve(entry.target);
            }
        });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });
    items.forEach(function (el) { io.observe(el); });

    var year = document.getElementById('year');
    if (year) year.textContent = new Date().getFullYear();
})();
