/**
 * AG Business Barber — JS runtime.
 *
 * Injecte 4 sections supplementaires sur la home (front-page.php du
 * theme Free) sans toucher au DOM Free verrouille :
 *   1. Notre equipe (barbiers)
 *   2. Galerie
 *   3. Temoignages
 *   4. Reservation calendrier + Contact/horaires
 *
 * Toutes les classes sont prefixees ag-bb-* (ag-business-barber).
 */
(function () {
	'use strict';

	function isActive() {
		return document.body.classList.contains('ag-bb-active');
	}
	function dataValue(key, fallback) {
		return (typeof agBbData !== 'undefined' && agBbData[key] != null) ? agBbData[key] : fallback;
	}
	function escapeHtml(s) {
		return String(s == null ? '' : s)
			.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
	}

	function renderTeam() {
		var team = dataValue('team', []);
		if (!team.length) return '';
		var cards = team.map(function (b) {
			return '' +
				'<article class="ag-bb-barber">' +
					'<div class="ag-bb-barber__photo" style="background-image:url(\'' + escapeHtml(b.photo) + '\');" aria-hidden="true"></div>' +
					'<div class="ag-bb-barber__body">' +
						'<h3 class="ag-bb-barber__name">' + escapeHtml(b.name) + '</h3>' +
						'<p class="ag-bb-barber__role">' + escapeHtml(b.role) + '</p>' +
						'<span class="ag-bb-barber__years">' + escapeHtml(b.years) + ' ans d\'expérience</span>' +
						'<p class="ag-bb-barber__specialty">' + escapeHtml(b.specialty) + '</p>' +
						'<a class="ag-bb-barber__insta" href="https://instagram.com/' + escapeHtml(String(b.insta).replace(/^@/, '')) + '" target="_blank" rel="noopener">' + escapeHtml(b.insta) + ' →</a>' +
					'</div>' +
				'</article>';
		}).join('');
		return '' +
			'<section class="ag-bb-section ag-bb-section--alt" id="ag-bb-team">' +
				'<div class="ag-bb-container">' +
					'<h2 class="ag-section__title">L\'<em>équipe</em></h2>' +
					'<p class="ag-section__sub">Des barbiers passionnés, formés à la vieille école américaine.</p>' +
					'<div class="ag-bb-team-grid">' + cards + '</div>' +
				'</div>' +
			'</section>';
	}

	function renderGallery() {
		var imgs = dataValue('gallery', []);
		if (!imgs.length) return '';
		var items = imgs.map(function (url) {
			return '<div class="ag-bb-gallery-item" style="background-image:url(\'' + escapeHtml(url) + '\');" aria-hidden="true"></div>';
		}).join('');
		return '' +
			'<section class="ag-bb-section" id="ag-bb-gallery">' +
				'<div class="ag-bb-container">' +
					'<h2 class="ag-section__title">La <em>galerie</em></h2>' +
					'<p class="ag-section__sub">Quelques coupes signées par l\'équipe.</p>' +
					'<div class="ag-bb-gallery-grid">' + items + '</div>' +
				'</div>' +
			'</section>';
	}

	function renderTestimonials() {
		var t = dataValue('testimonials', []);
		if (!t.length) return '';
		var cards = t.map(function (it) {
			return '' +
				'<article class="ag-bb-testimonial">' +
					'<p class="ag-bb-testimonial__quote">' + escapeHtml(it.quote) + '</p>' +
					'<footer class="ag-bb-testimonial__author">' +
						'<span class="ag-bb-testimonial__name">' + escapeHtml(it.name) + '</span>' +
						'<span class="ag-bb-testimonial__role">' + escapeHtml(it.role) + '</span>' +
					'</footer>' +
				'</article>';
		}).join('');
		return '' +
			'<section class="ag-bb-section ag-bb-section--alt" id="ag-bb-testimonials">' +
				'<div class="ag-bb-container">' +
					'<h2 class="ag-section__title">Ils <em>parlent de nous</em></h2>' +
					'<p class="ag-section__sub">Avis clients vérifiés.</p>' +
					'<div class="ag-bb-testimonials-grid">' + cards + '</div>' +
				'</div>' +
			'</section>';
	}

	function renderBooking() {
		var team = dataValue('team', []);
		var slots = dataValue('bookingSlots', []);
		var todayIso = new Date().toISOString().slice(0, 10);
		var barberOpts = '<option value="">— N\'importe lequel —</option>' + team.map(function (b) {
			return '<option value="' + escapeHtml(b.name) + '">' + escapeHtml(b.name) + '</option>';
		}).join('');
		var slotOpts = '<option value="">— Choisir un créneau —</option>' + slots.map(function (s) {
			return '<option value="' + escapeHtml(s) + '">' + escapeHtml(s) + '</option>';
		}).join('');
		var serviceOpts = [
			'Coupe homme', 'Coupe + barbe', 'Rasage traditionnel', 'Coupe enfant',
			'Coloration', 'Soin barbe', 'Forfait mariage'
		].map(function (s) { return '<option value="' + escapeHtml(s) + '">' + escapeHtml(s) + '</option>'; }).join('');

		return '' +
			'<section class="ag-bb-section" id="ag-bb-booking">' +
				'<div class="ag-bb-container">' +
					'<h2 class="ag-section__title">Réserver un <em>créneau</em></h2>' +
					'<p class="ag-section__sub">Plus simple qu\'un coup de fil. Confirmation immédiate par email.</p>' +
					'<form class="ag-bb-booking" method="post" action="#ag-bb-booking" onsubmit="return false;">' +
						'<div class="ag-bb-booking__row">' +
							'<div class="ag-bb-booking__field">' +
								'<label for="ag-bb-date">Date</label>' +
								'<input type="date" id="ag-bb-date" name="date" min="' + todayIso + '" required>' +
							'</div>' +
							'<div class="ag-bb-booking__field">' +
								'<label for="ag-bb-slot">Créneau</label>' +
								'<select id="ag-bb-slot" name="slot" required>' + slotOpts + '</select>' +
							'</div>' +
						'</div>' +
						'<div class="ag-bb-booking__row">' +
							'<div class="ag-bb-booking__field">' +
								'<label for="ag-bb-service">Prestation</label>' +
								'<select id="ag-bb-service" name="service" required>' + serviceOpts + '</select>' +
							'</div>' +
							'<div class="ag-bb-booking__field">' +
								'<label for="ag-bb-barber">Barbier</label>' +
								'<select id="ag-bb-barber" name="barber">' + barberOpts + '</select>' +
							'</div>' +
						'</div>' +
						'<div class="ag-bb-booking__row">' +
							'<div class="ag-bb-booking__field">' +
								'<label for="ag-bb-name">Prénom</label>' +
								'<input type="text" id="ag-bb-name" name="name" required>' +
							'</div>' +
							'<div class="ag-bb-booking__field">' +
								'<label for="ag-bb-phone">Téléphone</label>' +
								'<input type="tel" id="ag-bb-phone" name="phone" required>' +
							'</div>' +
						'</div>' +
						'<button type="submit" class="ag-bb-booking__submit">Confirmer la réservation</button>' +
						'<p class="ag-bb-booking__note">En cliquant, vous acceptez d\'être recontacté pour confirmation.</p>' +
					'</form>' +
				'</div>' +
			'</section>';
	}

	function renderContact() {
		var hours = dataValue('shopHours', []);
		var addr = dataValue('shopAddress', '');
		var phone = dataValue('shopPhone', '');
		var phoneClean = String(phone).replace(/[^0-9+]/g, '');
		var insta = dataValue('shopInsta', '');
		var instaHandle = String(insta).replace(/^@/, '');
		var hoursHtml = hours.map(function (h) {
			var isClosed = /ferme/i.test(h.hours);
			return '<li class="' + (isClosed ? 'closed' : '') + '"><strong>' + escapeHtml(h.day) + '</strong><span>' + escapeHtml(h.hours) + '</span></li>';
		}).join('');
		return '' +
			'<section class="ag-bb-section ag-bb-section--alt" id="ag-bb-contact">' +
				'<div class="ag-bb-container">' +
					'<h2 class="ag-section__title">Nous <em>trouver</em></h2>' +
					'<p class="ag-section__sub">Le salon est ouvert du mardi au samedi.</p>' +
					'<div class="ag-bb-contact-grid">' +
						'<div class="ag-bb-contact-block">' +
							'<h3>Coordonnées</h3>' +
							'<p>📍 ' + escapeHtml(addr) + '</p>' +
							'<p>📞 <a href="tel:' + escapeHtml(phoneClean) + '">' + escapeHtml(phone) + '</a></p>' +
							'<p>📷 <a href="https://instagram.com/' + escapeHtml(instaHandle) + '" target="_blank" rel="noopener">' + escapeHtml(insta) + '</a></p>' +
						'</div>' +
						'<div class="ag-bb-hours-block">' +
							'<h3>Horaires</h3>' +
							'<ul>' + hoursHtml + '</ul>' +
						'</div>' +
					'</div>' +
				'</div>' +
			'</section>';
	}

	function injectSections() {
		if (!isActive()) return;
		var main = document.querySelector('main') || document.getElementById('main');
		if (!main) return;
		// Restreint a la home : presence simultanee du hero + queue +
		// services. Sur page-queue.php / autres pages, on n'injecte pas.
		var isHome = !!document.querySelector('.ag-hero')
			&& !!document.querySelector('#queue')
			&& !!document.querySelector('#services');
		if (!isHome) return;
		if (document.getElementById('ag-bb-team')) return; // deja fait

		var html = renderTeam() + renderGallery() + renderTestimonials() + renderBooking() + renderContact();
		main.insertAdjacentHTML('beforeend', html);
	}

	/* ── Bouton WhatsApp flottant + badge Premium (style Back Alive) ── */
	function injectFloatingElements() {
		if (!isActive()) return;
		var phone = String(dataValue('shopPhone', '')).replace(/[^0-9]/g, '');
		// Si pas de tel : skip WhatsApp (evite un lien casse)
		if (phone && !document.querySelector('.ag-bb-whatsapp')) {
			var wa = document.createElement('a');
			wa.className = 'ag-bb-whatsapp';
			wa.href = 'https://wa.me/' + phone;
			wa.target = '_blank';
			wa.rel = 'noopener';
			wa.setAttribute('aria-label', 'Contacter sur WhatsApp');
			wa.innerHTML = '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>';
			document.body.appendChild(wa);
		}
		// Badge Premium en bas-droite
		if (!document.querySelector('.ag-bb-premium-badge')) {
			var badge = document.createElement('div');
			badge.className = 'ag-bb-premium-badge';
			badge.textContent = 'Premium';
			document.body.appendChild(badge);
		}
	}

	function run() {
		injectSections();
		injectFloatingElements();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', run);
	} else {
		run();
	}
})();
