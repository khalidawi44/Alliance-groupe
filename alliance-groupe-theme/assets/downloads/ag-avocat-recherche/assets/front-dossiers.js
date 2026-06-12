/* AG Recherche Juridique — façade : navigation + gestion des dossiers.
   (La recherche, l'IA et l'enregistrement des réglages sont gérés par recherche.js,
    qui réutilise les mêmes id/classes.) */
(function () {
	'use strict';

	function $(s, c) { return (c || document).querySelector(s); }
	function $all(s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); }

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
	function esc(s) { var d = document.createElement('div'); d.textContent = s == null ? '' : s; return d.innerHTML; }

	/* ---------- Navigation principale (Recherche / Dossiers / Réglages) ---------- */
	var dossiersLoaded = false;
	$all('.ag-jr-navbtn').forEach(function (b) {
		b.addEventListener('click', function () {
			$all('.ag-jr-navbtn').forEach(function (x) { x.classList.remove('is-active'); });
			$all('.ag-jr-section').forEach(function (x) { x.classList.remove('is-active'); });
			b.classList.add('is-active');
			var sec = $('.ag-jr-section[data-section="' + b.dataset.section + '"]');
			if (sec) { sec.classList.add('is-active'); }
			if (b.dataset.section === 'dossiers' && !dossiersLoaded) { loadList(); }
		});
	});

	/* ---------- Liste des dossiers ---------- */
	var allDossiers = [];
	function loadList() {
		dossiersLoaded = true;
		var list = $('#ag-jr-d-list');
		list.innerHTML = '<p class="ag-jr-empty">Chargement…</p>';
		post('ag_jr_dossier_list', {}, function (res) {
			if (!res.success) { list.innerHTML = '<p class="ag-jr-err">' + esc(res.data.message) + '</p>'; return; }
			allDossiers = res.data || [];
			renderList();
		});
	}
	function renderList() {
		var list = $('#ag-jr-d-list');
		var q = ($('#ag-jr-d-search').value || '').toLowerCase();
		var items = allDossiers.filter(function (d) {
			return !q || (d.title + ' ' + (d.domaine || '')).toLowerCase().indexOf(q) >= 0;
		});
		if (!items.length) {
			list.innerHTML = '<p class="ag-jr-empty">Aucun dossier. Cliquez sur « + Nouveau dossier ».</p>';
			return;
		}
		list.innerHTML = items.map(function (d) {
			return '<div class="ag-jr-d-item" data-id="' + d.id + '">'
				+ '<div class="ag-jr-d-info"><strong>' + esc(d.title) + '</strong>'
				+ '<span class="ag-jr-d-meta">' + (d.domaine ? esc(d.domaine) + ' · ' : '') + 'maj ' + esc(d.date) + '</span></div>'
				+ '<div class="ag-jr-d-act">'
				+ '<button class="ag-jr-link ag-jr-d-open" data-id="' + d.id + '">Ouvrir</button>'
				+ '<button class="ag-jr-link ag-jr-d-del" data-id="' + d.id + '">Supprimer</button>'
				+ '</div></div>';
		}).join('');
	}
	if ($('#ag-jr-d-search')) { $('#ag-jr-d-search').addEventListener('input', renderList); }
	if ($('#ag-jr-d-new')) { $('#ag-jr-d-new').addEventListener('click', function () { openEditor(0); }); }

	document.addEventListener('click', function (e) {
		var open = e.target.closest('.ag-jr-d-open');
		if (open) { openEditor(parseInt(open.dataset.id, 10)); return; }
		var del = e.target.closest('.ag-jr-d-del');
		if (del) {
			if (!confirm('Supprimer ce dossier ?')) { return; }
			post('ag_jr_dossier_delete', { id: del.dataset.id }, function (res) {
				if (res.success) { allDossiers = allDossiers.filter(function (d) { return d.id != del.dataset.id; }); renderList(); }
			});
		}
	});

	/* ---------- Éditeur de dossier ---------- */
	function fieldsHtml(vals) {
		vals = vals || {};
		var html = '<label class="ag-jr-f"><span>Titre du dossier</span><input type="text" id="ag-jr-f-title" value="' + esc(vals.__title || '') + '" placeholder="Ex : Dupont c/ Nantes Habitat — punaises de lit" /></label>';
		Object.keys(AGJR.fields).forEach(function (k) {
			var f = AGJR.fields[k];
			html += '<label class="ag-jr-f"><span>' + esc(f.label) + '</span>';
			if (f.type === 'area') {
				html += '<textarea id="ag-jr-f-' + k + '" rows="4">' + esc(vals[k] || '') + '</textarea>';
			} else {
				html += '<input type="text" id="ag-jr-f-' + k + '" value="' + esc(vals[k] || '') + '" />';
			}
			html += '</label>';
		});
		return html;
	}
	function editorShell(id) {
		return '<div class="ag-jr-d-editbox" data-id="' + id + '">'
			+ '<div class="ag-jr-d-edithead"><button class="ag-jr-link" id="ag-jr-d-back">← Retour à la liste</button>'
			+ '<span id="ag-jr-d-msg"></span></div>'
			+ '<div id="ag-jr-d-fields"></div>'
			+ '<div class="ag-jr-d-foot"><button class="ag-jr-btn ag-jr-btn-go" id="ag-jr-d-save">💾 Enregistrer</button> '
			+ '<a class="ag-jr-link" id="ag-jr-d-print" target="_blank" hidden>🖨️ Vue imprimable / PDF</a></div></div>';
	}
	function showEditor(id, vals) {
		var ed = $('#ag-jr-d-editor');
		$('#ag-jr-d-list').hidden = true;
		$('.ag-jr-dossiers-bar').hidden = true;
		ed.hidden = false;
		ed.innerHTML = editorShell(id);
		$('#ag-jr-d-fields').innerHTML = fieldsHtml(vals);
		$('#ag-jr-d-back').addEventListener('click', closeEditor);
		$('#ag-jr-d-save').addEventListener('click', function () { saveEditor(id); });
		if (id) {
			var pl = $('#ag-jr-d-print');
			pl.hidden = false;
		}
	}
	function openEditor(id) {
		if (!id) { showEditor(0, {}); return; }
		post('ag_jr_dossier_get', { id: id }, function (res) {
			if (!res.success) { alert(res.data.message); return; }
			var v = res.data.fields || {};
			v.__title = res.data.title;
			showEditor(id, v);
		});
	}
	function closeEditor() {
		$('#ag-jr-d-editor').hidden = true;
		$('#ag-jr-d-list').hidden = false;
		$('.ag-jr-dossiers-bar').hidden = false;
	}
	function saveEditor(id) {
		var data = { id: id, title: $('#ag-jr-f-title').value };
		Object.keys(AGJR.fields).forEach(function (k) {
			var el = $('#ag-jr-f-' + k);
			if (el) { data['f_' + k] = el.value; }
		});
		var msg = $('#ag-jr-d-msg');
		msg.textContent = 'Enregistrement…';
		post('ag_jr_dossier_save_front', data, function (res) {
			if (!res.success) { msg.textContent = '❌ ' + (res.data.message || 'Erreur'); return; }
			msg.textContent = '✅ Enregistré';
			var pl = $('#ag-jr-d-print');
			pl.href = res.data.print; pl.hidden = false;
			dossiersLoaded = false; // forcera le rechargement de la liste
			// met à jour l'id de l'éditeur si c'était une création
			$('#ag-jr-d-save').onclick = null;
			$('#ag-jr-d-save').addEventListener('click', function () { saveEditor(res.data.id); });
		});
	}
})();
