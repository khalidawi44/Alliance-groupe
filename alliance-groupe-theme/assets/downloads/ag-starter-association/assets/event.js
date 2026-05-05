/* Compteurs participants/supports avec animation au clic. */
(function () {
    var wrap = document.querySelector('.ag-evt-actions');
    if (!wrap || typeof agEvtCfg === 'undefined') return;

    var eventId = wrap.getAttribute('data-event-id');
    var nonce   = wrap.getAttribute('data-nonce');

    function animateCount(el, from, to) {
        var dur = 700, t0 = performance.now();
        function step(t) {
            var p = Math.min(1, (t - t0) / dur);
            // ease-out cubic
            var eased = 1 - Math.pow(1 - p, 3);
            var v = Math.round(from + (to - from) * eased);
            el.textContent = v;
            if (p < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }

    function spawnPlusOne(btn) {
        var bubble = document.createElement('span');
        bubble.className = 'ag-evt-action__bubble';
        bubble.textContent = '+1';
        btn.appendChild(bubble);
        setTimeout(function () { if (bubble.parentNode) bubble.parentNode.removeChild(bubble); }, 1100);
    }

    wrap.querySelectorAll('.ag-evt-action').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (btn.classList.contains('is-done') || btn.classList.contains('is-loading')) return;
            btn.classList.add('is-loading');
            var kind   = btn.getAttribute('data-action');
            var countEl = btn.querySelector('.ag-evt-action__count');
            var current = parseInt(countEl.getAttribute('data-count'), 10) || 0;

            var fd = new FormData();
            fd.append('action',   'ag_event_count');
            fd.append('event_id', eventId);
            fd.append('kind',     kind);
            fd.append('_wpnonce', nonce);

            fetch(agEvtCfg.ajax, { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    btn.classList.remove('is-loading');
                    btn.classList.add('is-done');
                    var newCount = (data && data.data && typeof data.data.count !== 'undefined') ? data.data.count : current + 1;
                    countEl.setAttribute('data-count', newCount);
                    animateCount(countEl, current, newCount);
                    spawnPlusOne(btn);
                })
                .catch(function () {
                    btn.classList.remove('is-loading');
                });
        });
    });
})();
