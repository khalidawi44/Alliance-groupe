/* AG Recherche Juridique — interface (vanilla JS, sans dépendance). */
(function () {
	'use strict';

	function $(s, c) { return (c || document).querySelector(s); }
	function $all(s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); }
	function enc(q) { return encodeURIComponent(q.trim()); }

	function post(action, data, cb) {
		var body = new URLSearchParams();
		body.append('action', action);
		body.append('nonce', AGJR.nonce);
		Object.keys(data || {}).forEach(function (k) { body.append(k, data[k]); });
		fetch(AGJR.ajax, { method: 'POST', credentials: 'same-origin', body: body })
			.then(function (r) { return r.json(); })
			.then(cb)
			.catch(function () { cb({ success: false, data: { message: 'Erreur réseau.' } }); });
	}

	/* ---------- Onglets ---------- */
	$all('.ag-jr-tab').forEach(function (t) {
		t.addEventListener('click', function () {
			$all('.ag-jr-tab').forEach(function (x) { x.classList.remove('is-active'); });
			$all('.ag-jr-panel').forEach(function (x) { x.classList.remove('is-active'); });
			t.classList.add('is-active');
			var p = $('.ag-jr-panel[data-panel="' + t.dataset.tab + '"]');
			if (p) { p.classList.add('is-active'); }
		});
	});

	/* ---------- Méta-moteur : rendu des sources ---------- */
	function renderSources() {
		var wrap = $('#ag-jr-sources');
		if (!wrap || !AGJR.sources) { return; }
		var html = '';
		Object.keys(AGJR.sources).forEach(function (cat) {
			html += '<div class="ag-jr-cat"><h3>' + cat + '</h3><div class="ag-jr-links">';
			AGJR.sources[cat].forEach(function (s) {
				html += '<a class="ag-jr-src" data-tpl="' + encodeURIComponent(s[1]) + '" target="_blank" rel="noopener">' + s[0] + '</a>';
			});
			html += '</div></div>';
		});
		wrap.innerHTML = html;
		refreshSourceLinks();
	}
	function refreshSourceLinks() {
		var q = ($('#ag-jr-q') ? $('#ag-jr-q').value : '').trim();
		$all('.ag-jr-src').forEach(function (a) {
			var tpl = decodeURIComponent(a.dataset.tpl);
			a.href = tpl.replace(/\{q\}/g, enc(q || ''));
			a.classList.toggle('is-ready', !!q);
		});
	}

	/* ---------- Recherche LIVE Judilibre ---------- */
	function juriLabel(code) {
		var m = { cc: 'Cour de cassation', ca: 'Cour d\'appel', tj: 'Tribunal judiciaire' };
		return m[code] || (code || '').toUpperCase();
	}
	function liveSearch() {
		var q = $('#ag-jr-q').value.trim();
		var box = $('#ag-jr-results');
		if (!q) { box.innerHTML = '<p class="ag-jr-empty">Tapez une recherche.</p>'; return; }
		if (!AGJR.live_on) {
			box.innerHTML = '<p class="ag-jr-empty">Recherche LIVE non configurée — voir l\'onglet « Toutes les sources » ou les Réglages.</p>';
			return;
		}
		box.innerHTML = '<p class="ag-jr-loading">Recherche en cours…</p>';
		post('ag_jr_judilibre', { q: q, jur: $('#ag-jr-jur').value, sort: $('#ag-jr-sort').value }, function (res) {
			if (!res.success) { box.innerHTML = '<p class="ag-jr-err">' + (res.data && res.data.message || 'Erreur') + '</p>'; return; }
			var d = res.data;
			if (!d.results.length) { box.innerHTML = '<p class="ag-jr-empty">Aucune décision trouvée. Essayez d\'autres mots-clés ou les autres sources.</p>'; return; }
			var html = '<p class="ag-jr-count">' + d.total + ' décision(s) — ' + d.results.length + ' affichée(s)</p>';
			d.results.forEach(function (r) {
				var meta = [juriLabel(r.jurisdiction), r.chamber, r.number, r.date].filter(Boolean).join(' · ');
				html += '<div class="ag-jr-card">'
					+ '<div class="ag-jr-card-meta">' + meta + (r.solution ? ' · <em>' + r.solution + '</em>' : '') + '</div>'
					+ (r.themes && r.themes.length ? '<div class="ag-jr-themes">' + r.themes.slice(0, 4).map(function (t) { return '<span>' + t + '</span>'; }).join('') + '</div>' : '')
					+ '<p class="ag-jr-summary">' + (r.summary || '—') + '</p>'
					+ '<div class="ag-jr-card-act">'
					+ (r.ecli ? '<a target="_blank" rel="noopener" href="https://www.google.com/search?q=' + enc(r.ecli) + '">🔗 ' + r.ecli + '</a>' : '')
					+ (r.id ? ' <button class="button-link ag-jr-fulltext" data-id="' + r.id + '">📄 Texte intégral</button>' : '')
					+ '</div><div class="ag-jr-fulltext-out"></div></div>';
			});
			box.innerHTML = html;
		});
	}

	document.addEventListener('click', function (e) {
		var ft = e.target.closest('.ag-jr-fulltext');
		if (ft) {
			var out = ft.closest('.ag-jr-card').querySelector('.ag-jr-fulltext-out');
			out.innerHTML = '<p class="ag-jr-loading">Chargement…</p>';
			post('ag_jr_decision', { id: ft.dataset.id }, function (res) {
				if (!res.success) { out.innerHTML = '<p class="ag-jr-err">' + (res.data.message || 'Erreur') + '</p>'; return; }
				out.innerHTML = '<div class="ag-jr-text">' + (res.data.text ? res.data.text.replace(/\n/g, '<br/>') : 'Texte non disponible.') + '</div>';
			});
		}
	});

	/* ---------- Bouton recherche ---------- */
	function go() {
		refreshSourceLinks();
		liveSearch();
	}
	if ($('#ag-jr-go')) { $('#ag-jr-go').addEventListener('click', go); }
	if ($('#ag-jr-q')) {
		$('#ag-jr-q').addEventListener('keydown', function (e) { if (e.key === 'Enter') { go(); } });
		$('#ag-jr-q').addEventListener('input', refreshSourceLinks);
	}
	if ($('#ag-jr-jur')) { $('#ag-jr-jur').addEventListener('change', liveSearch); }
	if ($('#ag-jr-sort')) { $('#ag-jr-sort').addEventListener('change', liveSearch); }

	/* ---------- Assistant IA ---------- */
	var aiTask = 'probleme';
	$all('.ag-jr-ai-task').forEach(function (b) {
		b.addEventListener('click', function () {
			$all('.ag-jr-ai-task').forEach(function (x) { x.classList.remove('is-active'); });
			b.classList.add('is-active');
			aiTask = b.dataset.task;
		});
	});
	if ($('#ag-jr-ai-run')) {
		$('#ag-jr-ai-run').addEventListener('click', function () {
			var input = $('#ag-jr-ai-input').value.trim();
			var out = $('#ag-jr-ai-out');
			if (!input) { out.innerHTML = '<p class="ag-jr-err">Donnez du contexte.</p>'; return; }
			out.innerHTML = '<p class="ag-jr-loading">Analyse en cours…</p>';
			post('ag_jr_ai', { task: aiTask, input: input }, function (res) {
				if (!res.success) { out.innerHTML = '<p class="ag-jr-err">' + (res.data.message || 'Erreur') + '</p>'; return; }
				out.innerHTML = '<div class="ag-jr-text">' + escapeHtml(res.data.text).replace(/\n/g, '<br/>') + '</div>'
					+ '<button class="button ag-jr-to-arg">➕ Enregistrer dans la banque d\'arguments</button>';
			});
		});
	}
	function escapeHtml(s) { var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }

	document.addEventListener('click', function (e) {
		if (e.target.classList.contains('ag-jr-to-arg')) {
			var txt = $('#ag-jr-ai-out .ag-jr-text');
			var title = prompt('Intitulé de l\'argument :', ($('#ag-jr-ai-input').value || '').slice(0, 60));
			if (!title) { return; }
			post('ag_jr_arg_add', { title: title, body: txt ? txt.innerHTML : '' }, function (res) {
				e.target.textContent = res.success ? '✅ Enregistré' : '❌ Erreur';
			});
		}
	});

	/* ---------- Réglages : sauvegarde AJAX ---------- */
	if ($('#ag-jr-save')) {
		$('#ag-jr-save').addEventListener('click', function () {
			var ids = ['ag_jr_piste_id', 'ag_jr_piste_secret', 'ag_jr_piste_apikey', 'ag_jr_piste_oauth', 'ag_jr_piste_scope', 'ag_jr_judilibre_url', 'ag_jr_ai_key', 'ag_jr_ai_model'];
			var data = {};
			ids.forEach(function (id) { var el = $('#' + id); if (el) { data[id] = el.value; } });
			var msg = $('#ag-jr-save-msg');
			msg.textContent = 'Enregistrement…';
			post('ag_jr_save_settings', data, function (res) {
				msg.textContent = res.success ? '✅ ' + res.data.message : '❌ ' + (res.data && res.data.message || 'Erreur');
			});
		});
	}

	/* ---------- Init ---------- */
	renderSources();
})();
