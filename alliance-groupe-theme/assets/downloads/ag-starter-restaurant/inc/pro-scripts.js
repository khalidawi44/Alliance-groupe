/**
 * AG Starter Pro — animations & sticky header.
 * Only loaded when the licence is Pro+.
 */
(function(){
    'use strict';

    // Sticky header
    var header = document.querySelector('.ag-header');
    if (header) {
        var scrolled = false;
        window.addEventListener('scroll', function(){
            var s = window.scrollY > 60;
            if (s !== scrolled) {
                scrolled = s;
                header.classList.toggle('scrolled', s);
            }
        }, {passive:true});
    }

    // Scroll animations (IntersectionObserver)
    if (!document.body.classList.contains('ag-has-animations')) return;

    var targets = document.querySelectorAll('.ag-fade-in,.ag-slide-left,.ag-slide-right,.ag-scale-in');
    if (!targets.length || !('IntersectionObserver' in window)) {
        targets.forEach(function(el){ el.classList.add('visible'); });
        return;
    }

    var obs = new IntersectionObserver(function(entries){
        entries.forEach(function(e){
            if (e.isIntersecting) {
                e.target.classList.add('visible');
                obs.unobserve(e.target);
            }
        });
    }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });

    targets.forEach(function(el){ obs.observe(el); });
})();
