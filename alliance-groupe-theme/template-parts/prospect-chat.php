<?php
/**
 * Équipe de prospection guidée (sans IA / sans clé) sous forme de chat.
 * Léo (prospection) oriente -> Sofia (architecte) conçoit -> Karim (souscription)
 * closer -> Nadia (gestionnaire) pour les clients existants. Capture des contacts.
 */
$ag_ajax  = admin_url( 'admin-ajax.php' );
$ag_nonce = wp_create_nonce( 'ag_lead' );
$ag_sites = home_url( '/sites-express' );
$ag_amb   = home_url( '/ambassadeurs' );
$ag_asso  = home_url( '/wordpress-association' );
$ag_cli   = home_url( '/espace-client' );
?>
<div class="ag-chat" id="ag-chat" data-ajax="<?php echo esc_url( $ag_ajax ); ?>" data-nonce="<?php echo esc_attr( $ag_nonce ); ?>">
	<button type="button" class="ag-chat__bubble" id="ag-chat-bubble" aria-label="Ouvrir le chat">
		<span class="ag-chat__bubble-ic"></span>
		<span class="ag-chat__teaser" id="ag-chat-teaser">Parle à l'équipe </span>
	</button>
	<div class="ag-chat__panel" id="ag-chat-panel" aria-hidden="true">
		<div class="ag-chat__head">
			<div><strong>L'équipe Alliance Groupe</strong><small>Léo · Sofia · Karim · Nadia </small></div>
			<button type="button" class="ag-chat__close" id="ag-chat-close" aria-label="Fermer"></button>
		</div>
		<div class="ag-chat__body" id="ag-chat-body"></div>
	</div>
</div>
<style>
.ag-chat{position:fixed;right:18px;bottom:18px;z-index:100001;font-family:var(--font-sans,Arial,sans-serif);}
.ag-chat__bubble{display:flex;align-items:center;gap:10px;padding:12px 16px 12px 14px;border:none;border-radius:100px;background:linear-gradient(135deg,#D4B45C,#F37A1F);color:#10100a;font-weight:800;cursor:pointer;box-shadow:0 10px 30px rgba(0,0,0,.4);}
.ag-chat__bubble-ic{font-size:1.4rem;line-height:1;}
.ag-chat__teaser{font-size:.95rem;white-space:nowrap;}
.ag-chat__panel{position:absolute;right:0;bottom:64px;width:350px;max-width:calc(100vw - 36px);background:#14131a;border:1px solid rgba(212,180,92,.4);border-radius:18px;overflow:hidden;box-shadow:0 24px 70px rgba(0,0,0,.6);display:none;}
.ag-chat__panel.is-open{display:block;animation:ag-chat-in .3s cubic-bezier(.16,1,.3,1);}
@keyframes ag-chat-in{from{opacity:0;transform:translateY(12px);}to{opacity:1;transform:none;}}
.ag-chat__head{display:flex;justify-content:space-between;align-items:center;gap:10px;padding:14px 16px;background:linear-gradient(135deg,rgba(212,180,92,.2),rgba(243,122,31,.08));border-bottom:1px solid rgba(212,180,92,.25);}
.ag-chat__head strong{color:#fff;display:block;font-size:.98rem;}
.ag-chat__head small{color:#bdb6a8;font-size:.76rem;}
.ag-chat__close{background:none;border:none;color:var(--color-text-muted);font-size:1.1rem;cursor:pointer;}
.ag-chat__body{padding:16px;max-height:62vh;overflow-y:auto;display:flex;flex-direction:column;gap:10px;}
.ag-chat-who{font-size:.74rem;color:#e8c766;font-weight:700;margin:4px 0 -4px 2px;}
.ag-chat-msg{background:rgba(255,255,255,.05);border:1px solid rgba(212,180,92,.16);color:#ececf2;padding:11px 14px;border-radius:14px 14px 14px 4px;font-size:.92rem;line-height:1.5;max-width:94%;}
.ag-chat-msg strong{color:#fff;}
.ag-chat-opt{display:flex;flex-direction:column;gap:8px;margin-top:2px;}
.ag-chat-opt button,.ag-chat-opt a{display:block;width:100%;text-align:left;padding:11px 14px;border-radius:12px;border:1px solid rgba(212,180,92,.35);background:rgba(212,180,92,.08);color:#fff;font-weight:700;font-size:.9rem;cursor:pointer;text-decoration:none;transition:background .2s,border-color .2s;}
.ag-chat-opt button:hover,.ag-chat-opt a:hover{background:rgba(212,180,92,.18);border-color:rgba(212,180,92,.6);color:#fff;}
.ag-chat-form{display:flex;flex-direction:column;gap:8px;position:relative;}
.ag-chat-form input,.ag-chat-form textarea{padding:10px 12px;border-radius:10px;border:1px solid rgba(212,180,92,.3);background:rgba(255,255,255,.06);color:#fff;font-size:.9rem;}
.ag-chat-hp{position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden;}
.ag-chat-send{background:linear-gradient(135deg,#D4B45C,#F37A1F)!important;color:#10100a!important;border:none!important;font-weight:800;}
@media(max-width:480px){.ag-chat__teaser{display:none;}.ag-chat__panel{bottom:70px;}}
</style>
<script>
(function(){
	var root=document.getElementById('ag-chat'); if(!root) return;
	var bubble=document.getElementById('ag-chat-bubble'),panel=document.getElementById('ag-chat-panel'),body=document.getElementById('ag-chat-body'),teaser=document.getElementById('ag-chat-teaser');
	var AJAX=root.getAttribute('data-ajax'),NONCE=root.getAttribute('data-nonce');
	var L={sites:<?php echo wp_json_encode( $ag_sites ); ?>,amb:<?php echo wp_json_encode( $ag_amb ); ?>,asso:<?php echo wp_json_encode( $ag_asso ); ?>,cli:<?php echo wp_json_encode( $ag_cli ); ?>};
	var started=false;
	function bot(who,html){ if(who){var w=document.createElement('div');w.className='ag-chat-who';w.textContent=who;body.appendChild(w);} var m=document.createElement('div');m.className='ag-chat-msg';m.innerHTML=html;body.appendChild(m);body.scrollTop=body.scrollHeight; }
	function opts(list){var w=document.createElement('div');w.className='ag-chat-opt';list.forEach(function(o){var el;if(o.href){el=document.createElement('a');el.href=o.href;}else{el=document.createElement('button');el.type='button';el.addEventListener('click',o.fn);}el.innerHTML=o.label;w.appendChild(el);});body.appendChild(w);body.scrollTop=body.scrollHeight;}

	function start(){ if(started) return; started=true;
		bot('Léo · Prospection','Salut Moi c\'est <strong>Léo</strong>, de l\'équipe Alliance Groupe. Je t\'oriente vers la bonne personne. Tu veux…');
		opts([
			{label:'Créer / concevoir un site',fn:archi},
			{label:'Gagner de l\'argent (ambassadeur)',fn:amb},
			{label:'Je suis déjà client',fn:manager},
			{label:'Autre question',fn:function(){ bot('Léo · Prospection','Pas de souci, laisse-moi tes coordonnées et ta question '); leadForm('Question'); }}
		]);
	}
	// Architecte : conçoit + recommande un pack
	function archi(){
		bot('Sofia · Architecte','Parfait, je suis <strong>Sofia</strong>, l\'architecte. C\'est pour quel type d\'activité ?');
		opts([
			{label:'Restaurant / commerce',fn:function(){ scope('restaurant'); }},
			{label:'Artisan / BTP',fn:function(){ scope('artisan'); }},
			{label:'Beauté / coiffure',fn:function(){ scope('beauté'); }},
			{label:'Indépendant / coach',fn:function(){ scope('indépendant'); }},
			{label:'Association',fn:asso},
			{label:'Autre',fn:function(){ scope('ton activité'); }}
		]);
	}
	function scope(sector){
		bot('Sofia · Architecte','Top. Pour <strong>'+sector+'</strong>, tu veux quoi exactement ?');
		opts([
			{label:'Une vitrine simple (me présenter + contact)',fn:function(){ subscribe('Essentiel','490','vitrine '+sector); }},
			{label:'Un site complet (pages, blog, prise de RDV)',fn:function(){ subscribe('Pro','890','site complet '+sector); }},
			{label:'Vendre en ligne (boutique)',fn:function(){ subscribe('Boutique','1490','boutique '+sector); }}
		]);
	}
	// Souscription : closer
	function subscribe(pack,price,ctx){
		bot('Karim · Souscription','Super — je suis <strong>Karim</strong>. Pour ça, le <strong>pack '+pack+' à '+price+' €</strong> est idéal : prix fixe, <strong>payable en 4×</strong>, livré en quelques jours, sans rendez-vous. On lance ?');
		opts([
			{label:'Commander maintenant',href:L.sites+'#packs'},
			{label:'Être rappelé(e) avant',fn:function(){ bot('Karim · Souscription','Avec plaisir, je te laisse mes collègues te recontacter '); leadForm('Pack '+pack+' ('+ctx+')'); }}
		]);
	}
	function amb(){
		bot('Léo · Prospection','Tu peux <strong>gagner 10 % sur chaque site vendu</strong>, depuis ton téléphone — outils fournis, gratuit, sans engagement.');
		opts([
			{label:'Rejoindre le programme',href:L.amb},
			{label:'Qu\'on me recontacte',fn:function(){ leadForm('Devenir ambassadeur'); }}
		]);
	}
	function asso(){
		bot('Sofia · Architecte','Pour une association, on offre un <strong>site 100% GRATUIT</strong> (loi 1901, syndicat, collectif…).');
		opts([
			{label:'Voir l\'offre gratuite',href:L.asso},
			{label:'Être recontacté(e)',fn:function(){ leadForm('Association - site gratuit'); }}
		]);
	}
	// Gestionnaire de clients
	function manager(){
		bot('Nadia · Gestion clients','Je suis <strong>Nadia</strong>, je m\'occupe des clients Tu veux…');
		opts([
			{label:'Suivre mon projet / mon espace',href:L.cli},
			{label:'Une retouche ou une question',fn:function(){ bot('Nadia · Gestion clients','Dis-moi tout, je transmets à l\'équipe '); leadForm('Client existant - suivi'); }}
		]);
	}
	function leadForm(interest){
		var f=document.createElement('form');f.className='ag-chat-form';
		f.innerHTML='<input type="text" name="name" placeholder="Ton prénom" autocomplete="name">'
			+'<input type="email" name="email" placeholder="Ton email" autocomplete="email">'
			+'<input type="tel" name="phone" placeholder="Ton téléphone (option)" autocomplete="tel">'
			+'<textarea name="message" rows="2" placeholder="Ton message (option)"></textarea>'
			+'<div class="ag-chat-hp"><input type="text" name="company" tabindex="-1" autocomplete="off"></div>'
			+'<button type="submit" class="ag-chat-send">Envoyer </button>';
		body.appendChild(f);body.scrollTop=body.scrollHeight;
		f.addEventListener('submit',function(e){
			e.preventDefault();
			var fd=new FormData(f);fd.append('action','ag_lead');fd.append('nonce',NONCE);fd.append('interest',interest);
			if(!fd.get('email')&&!fd.get('phone')){ bot(null,'Il me faut au moins un <strong>email</strong> ou un <strong>téléphone</strong> '); return; }
			var btn=f.querySelector('button');btn.disabled=true;btn.textContent='Envoi…';
			fetch(AJAX,{method:'POST',body:fd}).then(function(r){return r.json();}).then(function(j){
				f.remove();
				bot('Nadia · Gestion clients', (j&&j.success) ? 'Merci ! On te recontacte très vite. À bientôt ' : 'Hmm, ça n\'a pas marché. Écris-nous à <strong>contact@alliancegroupe-inc.com</strong>.');
			}).catch(function(){ f.remove(); bot(null,'Souci de connexion. Écris-nous à <strong>contact@alliancegroupe-inc.com</strong>.'); });
		});
	}
	function open(){ panel.classList.add('is-open'); panel.setAttribute('aria-hidden','false'); if(teaser)teaser.style.display='none'; start(); }
	function close(){ panel.classList.remove('is-open'); panel.setAttribute('aria-hidden','true'); }
	bubble.addEventListener('click',function(){ panel.classList.contains('is-open')?close():open(); });
	document.getElementById('ag-chat-close').addEventListener('click',close);
})();
</script>
