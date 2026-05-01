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
			return '<a href="' + escapeHtml(url) + '" target="_blank" rel="noopener" style="background-image:url(\'' + escapeHtml(url) + '\');" aria-label="Voir la photo en grand"></a>';
		}).join('');
		return '' +
			'<section class="ag-bb-section" id="ag-bb-gallery">' +
				'<div class="ag-bb-container">' +
					'<h2 class="ag-section__title">La <em>galerie</em></h2>' +
					'<p class="ag-section__sub">Quelques coupes signées. Plus de photos sur Instagram.</p>' +
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
		if (document.getElementById('ag-bb-team')) return; // deja fait

		var html = renderTeam() + renderGallery() + renderTestimonials() + renderBooking() + renderContact();
		main.insertAdjacentHTML('beforeend', html);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', injectSections);
	} else {
		injectSections();
	}
})();
