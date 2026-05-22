<?php
/**
 * Template Name: Studio
 *
 * Studio créatif des ambassadeurs : médiathèque ON-SITE (visuels prêts) +
 * générateur de vidéo et d'image, sans jamais quitter le site.
 * Accès protégé (ambassadeur/admin) dans inc/ag-espaces.php.
 */
get_header();

$u            = wp_get_current_user();
$kind         = function_exists( 'ag_espace_member_kind' ) ? ag_espace_member_kind() : '';
$is_admin     = ( 'admin' === $kind );
$is_seller    = in_array( $kind, array( 'ambassadeur', 'admin' ), true ); // a son propre lien + compte au classement
$email        = ( $u && $u->ID ) ? $u->user_email : '';
// L'admin compte aussi dans le classement : on lui garantit une fiche + un code de parrainage.
if ( $is_admin && function_exists( 'ag_ensure_ambassador_for_user' ) ) { ag_ensure_ambassador_for_user( $u ); }
// Le lien intégré aux contenus = celui du vendeur connecté ; sinon lien simple du site (sans code).
$ag_sale_link    = ( $is_seller && function_exists( 'ag_ambassadeur_sale_link' ) )    ? ag_ambassadeur_sale_link( $email )    : home_url( '/sites-express' );
$ag_recruit_link = ( $is_seller && function_exists( 'ag_ambassadeur_recruit_link' ) ) ? ag_ambassadeur_recruit_link( $email ) : home_url( '/ambassadeurs' );

$studio_uri = get_stylesheet_directory_uri() . '/assets/images/studio/';
$prod_uri   = get_stylesheet_directory_uri() . '/assets/images/produits/';
$media = array();
foreach ( glob( get_stylesheet_directory() . '/assets/images/studio/*.jpg' ) as $f ) { $media[] = $studio_uri . basename( $f ); }
foreach ( glob( get_stylesheet_directory() . '/assets/images/produits/*.jpg' ) as $f ) { $media[] = $prod_uri . basename( $f ); }

// Fonds PROPRES (sans texte) pour le créateur de vidéo : évite le « texte sur texte ».
$cities_uri = get_stylesheet_directory_uri() . '/assets/images/cities/';
$bg = array();
foreach ( glob( get_stylesheet_directory() . '/assets/images/cities/*.jpg' ) as $f ) { $bg[] = $cities_uri . basename( $f ); }
?>
<main id="ag-main-content" class="ag-esp ag-studio">
	<section class="ag-section ag-section--graphite">
		<div class="ag-container">
			<div class="ag-esp-head">
				<div><span class="ag-tag">Studio créatif 🎬</span><h1 class="ag-section__title">Crée &amp; partage, sans quitter le site</h1></div>
				<?php if ( $is_seller ) : ?>
					<a href="<?php echo esc_url( home_url( '/espace-ambassadeur' ) ); ?>" class="ag-btn-outline ag-esp-logout">← Mon compte</a>
				<?php else : ?>
					<a href="<?php echo esc_url( home_url( '/ambassadeurs' ) ); ?>" class="ag-btn-gold ag-esp-logout">Gagner 10 % — devenir ambassadeur →</a>
				<?php endif; ?>
			</div>
			<?php if ( $is_seller ) : ?>
				<?php get_template_part( 'template-parts/espace-nav', null, array( 'active' => 'studio' ) ); ?>
			<?php endif; ?>
			<p class="ag-section__desc">Crée une vidéo ou une image pro en quelques secondes, et partage-la direct. <?php echo $is_seller ? 'Ton lien de vente est toujours intégré.' : 'C\'est gratuit pour tout le monde.'; ?></p>
			<?php if ( ! $is_seller ) : ?>
				<div class="ag-studio-guest">💡 Tu peux créer et partager librement — le contenu renvoie vers le site. <strong><?php echo is_user_logged_in() ? 'Deviens ambassadeur' : 'Connecte-toi ou deviens ambassadeur'; ?></strong> pour intégrer <strong>TON lien</strong> et toucher <strong>10 % sur chaque vente</strong>. <a href="<?php echo esc_url( home_url( '/ambassadeurs' ) ); ?>">En savoir plus →</a></div>
			<?php endif; ?>
			<div class="ag-mode">
				<button type="button" class="ag-mode-btn is-active" data-mode="sale" onclick="agSetMode('sale')">🛒 Vendre des sites</button>
				<button type="button" class="ag-mode-btn" data-mode="recruit" onclick="agSetMode('recruit')">🤝 Recruter mon équipe</button>
			</div>
			<p class="ag-mode-hint" id="ag-mode-hint">Mode <strong>Vente</strong> : ton lien mène à tes offres (ta commission). Passe en <strong>Recrutement</strong> pour partager ton lien d'équipe (tu gagnes sur leurs ventes).</p>
			<div class="ag-ct" role="tablist">
				<button type="button" class="ag-ct-btn is-active" data-pane="video" role="tab">🎬 Vidéo</button>
				<button type="button" class="ag-ct-btn" data-pane="image" role="tab">🖼️ Image</button>
			</div>
		</div>
	</section>
	<script>
	window.AG_SALE = { link: <?php echo wp_json_encode( $ag_sale_link ); ?>, caption: <?php echo wp_json_encode( 'Ton site pro à prix fixe, livré en quelques jours — dès 490 €. 👉 ' . $ag_sale_link ); ?> };
	window.AG_RECRUIT = { link: <?php echo wp_json_encode( $ag_recruit_link ); ?>, caption: <?php echo wp_json_encode( 'Gagne de l\'argent en vendant des sites — 10 % par vente, depuis ton tel. Rejoins l\'équipe 👉 ' . $ag_recruit_link ); ?> };
	window.AG_LINK = window.AG_SALE.link; window.AG_CAPTION = window.AG_SALE.caption;
	window.agSetMode = function(m){
		var d = ( m === 'recruit' ) ? window.AG_RECRUIT : window.AG_SALE;
		window.AG_LINK = d.link; window.AG_CAPTION = d.caption;
		var defs = ( m === 'recruit' )
			? { t:['Gagne de l\'argent','depuis ton téléphone','Vends des sites · 10 % par vente','Rejoins l\'équipe 👇'], ih:'Deviens commercial.', is:'Travaille depuis ton téléphone' }
			: { t:['Du quartier à la réussite.','Vends des sites depuis ton tel.','Touche 10 % sur chaque vente.',''], ih:'Ton site pro, dès 490 €', is:'Livré en quelques jours · sans rendez-vous' };
		var e;
		['ag-t1','ag-t2','ag-t3','ag-t4'].forEach(function(id,i){ var el=document.getElementById(id); if(el) el.value=defs.t[i]||''; });
		if((e=document.getElementById('ag-head')))  e.value=defs.ih;
		if((e=document.getElementById('ag-sub')))   e.value=defs.is;
		if(window.agVideoRefresh) window.agVideoRefresh();
		if(window.agImageRefresh) window.agImageRefresh();
		document.querySelectorAll('.ag-mode-btn').forEach(function(b){ b.classList.toggle('is-active', b.getAttribute('data-mode')===m); });
		var h=document.getElementById('ag-mode-hint');
		if(h) h.innerHTML = (m==='recruit')
			? 'Mode <strong>Recrutement</strong> : ton lien mène à l\'inscription ambassadeur (tu gagnes sur les ventes de ton équipe).'
			: 'Mode <strong>Vente</strong> : ton lien mène à tes offres (ta commission de 10 %).';
	};
	(function(){ try{ if(new URLSearchParams(location.search).get('mode')==='recruit'){ var go=function(){ window.agSetMode('recruit'); }; if(document.readyState!=='loading') go(); else document.addEventListener('DOMContentLoaded', go); } }catch(e){} })();
	// Bascule Vidéo / Image (deux boutons en haut)
	(function(){
		var btns=document.querySelectorAll('.ag-ct-btn'), panes=document.querySelectorAll('.ag-pane');
		function show(p){ btns.forEach(function(b){ b.classList.toggle('is-active', b.getAttribute('data-pane')===p); }); panes.forEach(function(s){ s.style.display = s.classList.contains('ag-pane--'+p) ? '' : 'none'; }); }
		btns.forEach(function(b){ b.addEventListener('click',function(){ show(b.getAttribute('data-pane')); }); });
	})();
	</script>

	<section class="ag-section ag-section--graphite ag-pane ag-pane--video" id="studio-video">
		<div class="ag-container ag-container--narrow">
			<h2 class="ag-section__title">Ta vidéo (8 s) 🎬</h2>
			<p class="ag-section__desc">Choisis un fond (ou ta propre photo/vidéo), écris tes textes : ils <strong>s'enchaînent</strong> dans la vidéo (titre, puis messages). Choisis la police et la couleur. Ton lien va dans la légende (copiée automatiquement au partage).</p>
			<div class="ag-maker ag-maker--v">
				<div class="ag-maker__preview"><canvas id="ag-vcanvas" width="1080" height="1920"></canvas></div>
				<div class="ag-maker__ctrl">
					<?php if ( $bg ) : ?>
					<div class="ag-bgpick">
						<span class="ag-bgpick__lbl">Choisis un fond</span>
						<div class="ag-bgpick__row">
							<?php foreach ( $bg as $i => $b ) : ?>
								<button type="button" class="ag-bgpick__thumb ag-vbg<?php echo 0 === $i ? ' is-active' : ''; ?>" data-src="<?php echo esc_url( $b ); ?>" style="background-image:url('<?php echo esc_url( $b ); ?>');" aria-label="Fond <?php echo (int) $i + 1; ?>"></button>
							<?php endforeach; ?>
						</div>
					</div>
					<?php endif; ?>
					<label>Ou ta photo / vidéo<input type="file" id="ag-vfile" accept="image/*,video/*"></label>
					<span class="ag-fields__lbl">Tes textes (ils s'enchaînent dans la vidéo)</span>
					<input type="text" id="ag-t1" class="ag-tinput" value="Du quartier à la réussite." maxlength="60" placeholder="Texte 1 (titre)">
					<input type="text" id="ag-t2" class="ag-tinput" value="Vends des sites depuis ton tel." maxlength="60" placeholder="Texte 2">
					<input type="text" id="ag-t3" class="ag-tinput" value="Touche 10 % sur chaque vente." maxlength="60" placeholder="Texte 3">
					<input type="text" id="ag-t4" class="ag-tinput" value="" maxlength="60" placeholder="Texte 4 (optionnel)">
					<div class="ag-opt">
						<span class="ag-opt__lbl">Police</span>
						<div class="ag-seg"><button type="button" class="ag-font-btn is-active" data-font="serif" style="font-family:Georgia,serif;">Serif</button><button type="button" class="ag-font-btn" data-font="sans" style="font-family:Arial,sans-serif;">Sans serif</button></div>
					</div>
					<div class="ag-opt">
						<span class="ag-opt__lbl">Couleur du texte</span>
						<div class="ag-colors">
							<button type="button" class="ag-color-btn is-active" data-color="#ffffff" style="background:#fff;" aria-label="Blanc"></button>
							<button type="button" class="ag-color-btn" data-color="#E8C766" style="background:#E8C766;" aria-label="Or"></button>
							<button type="button" class="ag-color-btn" data-color="#F37A1F" style="background:#F37A1F;" aria-label="Orange"></button>
							<button type="button" class="ag-color-btn" data-color="#4bbf77" style="background:#4bbf77;" aria-label="Vert"></button>
							<button type="button" class="ag-color-btn" data-color="#5ab0ff" style="background:#5ab0ff;" aria-label="Bleu"></button>
							<button type="button" class="ag-color-btn" data-color="#ff5d8f" style="background:#ff5d8f;" aria-label="Rose"></button>
						</div>
					</div>
					<div class="ag-dur">
						<span class="ag-dur__lbl">Durée de la vidéo</span>
						<div class="ag-dur__row">
							<button type="button" class="ag-dur-btn is-active" data-sec="8">8 s</button>
							<button type="button" class="ag-dur-btn" data-sec="16">16 s</button>
							<button type="button" class="ag-dur-btn" data-sec="24">24 s</button>
							<button type="button" class="ag-dur-btn ag-dur-btn--pro" data-sec="60">1 min 💰</button>
						</div>
						<span class="ag-dur__hint">💡 À partir d'<strong>1 minute</strong>, tes vidéos peuvent être <strong>monétisées</strong> sur TikTok.</span>
					</div>
					<button type="button" class="ag-btn-gold" id="ag-vgen">🎬 Générer ma vidéo</button>
					<p class="ag-vstatus" id="ag-vstatus"></p>
					<button type="button" class="ag-btn-gold ag-vshare-btn" id="ag-vshare" style="display:none;">
						<span class="ag-vshare-ic" aria-hidden="true"><span class="ag-vsi ag-vsi--tt">🎵</span><span class="ag-vsi ag-vsi--snap">👻</span><span class="ag-vsi ag-vsi--ig">📸</span></span>
						<span>Partager ma vidéo</span>
					</button>
					<button type="button" class="ag-btn-outline" id="ag-vcap" style="display:none;">📋 Copier la légende (+ ton lien)</button>
					<p class="ag-vhint" id="ag-vhint" style="display:none;">En touchant <strong>Partager</strong>, <strong>ta légende (avec ton lien) est copiée automatiquement</strong>. Choisis ton appli (ou « Enregistrer la vidéo »), puis dans TikTok/Snap : appui long → <strong>Coller</strong>. (TikTok n'accepte pas de légende auto, c'est la seule façon.)</p>
				</div>
			</div>
		</div>
	</section>
	<script>
	(function(){
		var cv=document.getElementById('ag-vcanvas'); if(!cv) return;
		var ctx=cv.getContext('2d'), W=cv.width, H=cv.height;
		var link=window.AG_LINK, caption=window.AG_CAPTION;
		var media=null, isVideo=false, lastBlob=null, lastExt='webm', lastUrl=null, previewRAF=null;
		function stopPreview(){ if(previewRAF){ cancelAnimationFrame(previewRAF); previewRAF=null; } }
		function startPreview(){ stopPreview(); var t0=performance.now(); (function loop(now){ frame(0,(now-t0)/1000,2.8); previewRAF=requestAnimationFrame(loop); })(performance.now()); }
		function renderOnce(){ startPreview(); }
		function startVideoPreview(){ startPreview(); }
		window.agVideoRefresh=function(){ link=window.AG_LINK; caption=window.AG_CAPTION; startPreview(); };
		var statusEl=document.getElementById('ag-vstatus'), shEl=document.getElementById('ag-vshare'), capEl=document.getElementById('ag-vcap'), hintEl=document.getElementById('ag-vhint');
		function getSegments(){ var out=[]; ['ag-t1','ag-t2','ag-t3','ag-t4'].forEach(function(id){ var e=document.getElementById(id); if(e&&e.value.trim()) out.push(e.value.trim()); }); if(!out.length) out.push('Alliance Groupe'); return out; }
		function agFont(){ var e=document.querySelector('.ag-font-btn.is-active'); return (e&&e.getAttribute('data-font')==='sans')?'Helvetica, Arial, sans-serif':'Georgia, serif'; }
		function agColor(){ var e=document.querySelector('.ag-color-btn.is-active'); return e?e.getAttribute('data-color'):'#ffffff'; }
		function wrapC(text,cx,cy,maxW,lh){var words=(text||'').split(' '),lines=[],line='';for(var i=0;i<words.length;i++){var t=line?line+' '+words[i]:words[i];if(ctx.measureText(t).width>maxW&&line){lines.push(line);line=words[i];}else line=t;}if(line)lines.push(line);var sy=cy-(lines.length-1)*lh/2;for(var j=0;j<lines.length;j++){ctx.fillText(lines[j],cx,sy+j*lh);}}
		function frame(p,tsec,segDur){
			ctx.fillStyle='#0e0d11';ctx.fillRect(0,0,W,H);
			if(media){var mw=isVideo?media.videoWidth:media.width,mh=isVideo?media.videoHeight:media.height;if(mw&&mh){var ir=mw/mh,cr=W/H,dw,dh,z=1.04;if(ir>cr){dh=H*z;dw=dh*ir;}else{dw=W*z;dh=dw/ir;}ctx.drawImage(media,(W-dw)/2,(H-dh)/2,dw,dh);}}
			var g=ctx.createLinearGradient(0,0,0,H);g.addColorStop(0,'rgba(7,7,12,.5)');g.addColorStop(.5,'rgba(7,7,12,.12)');g.addColorStop(1,'rgba(7,7,12,.78)');ctx.fillStyle=g;ctx.fillRect(0,0,W,H);
			ctx.textAlign='center';ctx.textBaseline='alphabetic';ctx.shadowColor='rgba(0,0,0,.75)';ctx.shadowBlur=14;
			ctx.globalAlpha=1;ctx.fillStyle='#D4B45C';ctx.font='bold 38px Georgia, serif';ctx.fillText('ALLIANCE GROUPE',W/2,112);
			var segs=getSegments(),n=segs.length,sd=segDur||2.8,t=(tsec===undefined)?0:tsec;
			var raw=Math.floor(t/sd),local=(t/sd)-raw,idx=((raw%n)+n)%n;
			var a=(tsec===undefined)?1:(local<0.18?local/0.18:(local>0.82?(1-local)/0.18:1));
			var txt=segs[idx],size=txt.length>44?60:(txt.length>24?78:104);
			ctx.globalAlpha=Math.max(0,Math.min(1,a));ctx.fillStyle=agColor();ctx.font='bold '+size+'px '+agFont();ctx.textBaseline='middle';
			wrapC(txt,W/2,H*0.6,W-150,size*1.12);
			ctx.globalAlpha=1;ctx.shadowBlur=0;ctx.textBaseline='alphabetic';
		}
		function loadFile(f){
			stopPreview();
			var url=URL.createObjectURL(f);
			if(/^video\//.test(f.type)){
				var v=document.createElement('video');
				v.muted=true; v.defaultMuted=true; v.loop=true; v.playsInline=true;
				v.setAttribute('muted',''); v.setAttribute('playsinline',''); v.preload='auto';
				v.style.cssText='position:fixed;right:0;bottom:0;width:2px;height:2px;opacity:0.01;pointer-events:none;z-index:-1;';
				document.body.appendChild(v);
				v.src=url;
				var ready=function(){ if(media===v) return; isVideo=true; media=v; v.play().catch(function(){}); startVideoPreview(); };
				v.addEventListener('loadeddata', ready);
				v.addEventListener('loadedmetadata', ready);
				v.load();
			} else {
				var im=new Image(); im.onload=function(){ isVideo=false; media=im; renderOnce(); }; im.src=url;
			}
		}
		function loadUrl(url,cb){var im=new Image();im.onload=function(){isVideo=false;media=im;renderOnce();if(cb)cb();};im.src=url;}
		function setBg(url,el){ loadUrl(url); document.querySelectorAll('.ag-vbg').forEach(function(t){ t.classList.toggle('is-active', t===el); }); }
		document.querySelectorAll('.ag-vbg').forEach(function(b){ b.addEventListener('click',function(){ setBg(b.getAttribute('data-src'), b); }); });
		(function(){ var first=document.querySelector('.ag-vbg'); if(first){ loadUrl(first.getAttribute('data-src')); } })();
		document.getElementById('ag-vfile').addEventListener('change',function(e){var f=e.target.files&&e.target.files[0];if(f)loadFile(f);});
		document.querySelectorAll('.ag-font-btn').forEach(function(b){ b.addEventListener('click',function(){ document.querySelectorAll('.ag-font-btn').forEach(function(x){ x.classList.toggle('is-active', x===b); }); }); });
		document.querySelectorAll('.ag-color-btn').forEach(function(b){ b.addEventListener('click',function(){ document.querySelectorAll('.ag-color-btn').forEach(function(x){ x.classList.toggle('is-active', x===b); }); }); });
		function agSelectedDuration(){ var el=document.querySelector('.ag-dur-btn.is-active'); return el ? parseInt(el.getAttribute('data-sec'),10)*1000 : 8000; }
		document.querySelectorAll('.ag-dur-btn').forEach(function(b){ b.addEventListener('click',function(){ document.querySelectorAll('.ag-dur-btn').forEach(function(x){ x.classList.toggle('is-active', x===b); }); }); });
		document.getElementById('ag-vgen').addEventListener('click',function(){
			if(!media){statusEl.textContent='Choisis un fond ou ajoute une photo/vidéo.';return;}
			if(!cv.captureStream||!window.MediaRecorder){statusEl.textContent='Ton navigateur ne permet pas l\'enregistrement. Essaie Chrome (Android/PC).';return;}
			stopPreview();
			var mime=['video/mp4;codecs=h264','video/mp4','video/webm;codecs=h264','video/webm;codecs=vp9','video/webm;codecs=vp8','video/webm'].find(function(m){return MediaRecorder.isTypeSupported&&MediaRecorder.isTypeSupported(m);})||'';
			// Enregistrement via un canvas hors-ecran en 720x1280 : bien plus fluide (surtout pour les videos importees), sans toucher au canvas visible.
			var RW=720,RH=1280,recCv=document.createElement('canvas');recCv.width=RW;recCv.height=RH;var recCtx=recCv.getContext('2d');
			var stream=recCv.captureStream(30), rec=null, tries=[];
			if(mime){ tries.push({mimeType:mime}); }
			tries.push(undefined);
			for(var ti=0;ti<tries.length;ti++){ try{ rec=new MediaRecorder(stream,tries[ti]); break; }catch(e){} }
			if(!rec){ statusEl.textContent='Enregistrement non supporté sur ce navigateur. Essaie une autre appli ou mets à jour ton téléphone.'; return; }
			var chunks=[];
			rec.ondataavailable=function(ev){if(ev.data&&ev.data.size)chunks.push(ev.data);};
			rec.onstop=function(){
				var actual=(rec.mimeType||mime||'video/webm');
				lastExt=(actual.indexOf('mp4')>-1?'mp4':'webm');
				lastBlob=new Blob(chunks,{type:actual});
				if(lastUrl) URL.revokeObjectURL(lastUrl); lastUrl=URL.createObjectURL(lastBlob);
				shEl.style.display='inline-flex';capEl.style.display='inline-flex';hintEl.style.display='block';
				statusEl.textContent='✓ Vidéo prête ! Partage-la ou enregistre-la juste en dessous 👇';
				if(isVideo) startVideoPreview(); else renderOnce();
			};
			if(isVideo){try{media.currentTime=0;media.play();}catch(e){}}
			var DUR=agSelectedDuration(),secs=Math.round(DUR/1000),start=performance.now();
			var nSeg=getSegments().length,segDur=(DUR/1000)/Math.max(1,nSeg);
			try{ rec.start(); }catch(e){ statusEl.textContent='Impossible de démarrer l\'enregistrement sur ce navigateur.'; return; }
			statusEl.textContent='Génération en cours… ('+secs+' s) — laisse l\'écran allumé';
			document.getElementById('ag-vgen').disabled=true;
			var stopAt=start+DUR;
			function drawF(){var el=performance.now()-start,p=Math.min(1,el/DUR);frame(p,el/1000,segDur);recCtx.drawImage(cv,0,0,RW,RH);}
			function finishRec(){try{rec.stop();}catch(e){}if(isVideo){try{media.pause();}catch(e){}}document.getElementById('ag-vgen').disabled=false;}
			if(isVideo && media.requestVideoFrameCallback){
				// On dessine a chaque IMAGE de la video source -> cadence collee a la video -> fluide.
				var vcb=function(){ drawF(); if(performance.now()<stopAt){ try{ media.requestVideoFrameCallback(vcb); }catch(e){ requestAnimationFrame(vcb); } } else { finishRec(); } };
				media.requestVideoFrameCallback(vcb);
			} else {
				(function tick(){ drawF(); if(performance.now()<stopAt) requestAnimationFrame(tick); else finishRec(); })();
			}
		});
		function saveAndGuide(){
			var a=document.createElement('a');a.href=lastUrl||URL.createObjectURL(lastBlob);a.download='alliance-video.'+lastExt;document.body.appendChild(a);a.click();a.remove();
			statusEl.textContent='Vidéo enregistrée 📁 + légende copiée ✓ Ouvre TikTok/Snap/Insta, choisis la vidéo dans ta galerie, colle la légende.';
		}
		function agShareVideo(){
			if(!lastBlob){ statusEl.textContent='Génère d\'abord ta vidéo (bouton ci-dessus).'; return; }
			// Copie AUTO la légende (avec le lien) : plus qu'à coller dans TikTok/Snap.
			try{ if(navigator.clipboard) navigator.clipboard.writeText(caption); }catch(e){}
			// Fichier SEUL (sans texte/titre) : c'est ce qui fait apparaitre le plus d'apps (TikTok, Insta, Snap) dans le menu iOS.
			var file=new File([lastBlob],'alliance-video.'+lastExt,{type:lastBlob.type||'video/mp4'});
			if(navigator.canShare && navigator.canShare({files:[file]})){
				navigator.share({files:[file]}).then(function(){
					statusEl.textContent='✓ Légende déjà copiée ! Dans TikTok/Snap : appui long sur le champ texte → « Coller », puis publie.';
				}).catch(function(err){
					if(err && err.name==='AbortError') return;
					saveAndGuide();
				});
			} else {
				saveAndGuide();
			}
		}
		shEl.addEventListener('click',agShareVideo);
		capEl.addEventListener('click',function(){ navigator.clipboard.writeText(caption).then(function(){ capEl.textContent='✓ Légende copiée'; setTimeout(function(){ capEl.textContent='📋 Copier la légende (+ ton lien)'; },1600); }); });
		startPreview();
	})();
	</script>

	<section class="ag-section ag-section--onyx ag-pane ag-pane--image" style="display:none;">
		<div class="ag-container ag-container--narrow">
			<h2 class="ag-section__title">Ton image perso 🖌️</h2>
			<p class="ag-section__desc">Ta photo + ton accroche → image brandée avec ton lien, prête à poster.</p>
			<div class="ag-maker">
				<div class="ag-maker__preview"><canvas id="ag-canvas" width="1080" height="1350"></canvas></div>
				<div class="ag-maker__ctrl">
					<label>Ta photo<input type="file" id="ag-img" accept="image/*"></label>
					<label>Ton accroche<input type="text" id="ag-head" value="Ton site pro, dès 490 €" maxlength="48"></label>
					<label>Sous-texte<input type="text" id="ag-sub" value="Livré en quelques jours · sans rendez-vous" maxlength="64"></label>
					<button type="button" class="ag-btn-gold" id="ag-imgshare">⤴ Partager l'image (TikTok, Snap…)</button>
					<button type="button" class="ag-btn-outline" id="ag-dl">⬇ Télécharger</button>
				</div>
			</div>
		</div>
	</section>
	<script>
	(function(){
		var canvas=document.getElementById('ag-canvas'); if(!canvas) return;
		var ctx=canvas.getContext('2d'), W=canvas.width, H=canvas.height, userImg=null;
		var link=window.AG_LINK;
		window.agImageRefresh=function(){ link=window.AG_LINK; draw(); };
		function wrap(text,x,y,maxW,lh){var words=(text||'').split(' '),line='',yy=y;for(var i=0;i<words.length;i++){var t=line+words[i]+' ';if(ctx.measureText(t).width>maxW&&i>0){ctx.fillText(line.trim(),x,yy);line=words[i]+' ';yy+=lh;}else line=t;}ctx.fillText(line.trim(),x,yy);return yy;}
		function draw(){
			ctx.fillStyle='#0e0d11';ctx.fillRect(0,0,W,H);
			if(userImg){var ir=userImg.width/userImg.height,cr=W/H,dw,dh,dx,dy;if(ir>cr){dh=H;dw=H*ir;dx=(W-dw)/2;dy=0;}else{dw=W;dh=W/ir;dx=0;dy=(H-dh)/2;}ctx.drawImage(userImg,dx,dy,dw,dh);}
			else{ctx.fillStyle='#1a1820';ctx.fillRect(0,0,W,H);ctx.fillStyle='#6b6675';ctx.font='34px Arial';ctx.textAlign='center';ctx.fillText('Ajoute ta photo ↑',W/2,H/2);}
			var g=ctx.createLinearGradient(0,H*0.4,0,H);g.addColorStop(0,'rgba(7,7,12,0)');g.addColorStop(1,'rgba(7,7,12,.94)');ctx.fillStyle=g;ctx.fillRect(0,0,W,H);
			ctx.shadowColor='rgba(0,0,0,.6)';ctx.shadowBlur=12;
			ctx.textAlign='left';ctx.fillStyle='#D4B45C';ctx.font='bold 36px Georgia, serif';ctx.fillText('ALLIANCE GROUPE',60,84);
			ctx.fillStyle='#ffffff';ctx.font='bold 70px Georgia, serif';wrap((document.getElementById('ag-head').value||''),60,H-250,W-120,78);
			ctx.fillStyle='#E8C766';ctx.font='600 36px Arial';ctx.fillText((document.getElementById('ag-sub').value||''),60,H-150);
			ctx.shadowBlur=0;ctx.fillStyle='#cfc7b8';ctx.font='600 32px Arial';ctx.fillText(link.replace(/^https?:\/\//,''),60,H-88);
		}
		document.getElementById('ag-img').addEventListener('change',function(e){var f=e.target.files&&e.target.files[0];if(!f)return;var img=new Image();img.onload=function(){userImg=img;draw();};img.src=URL.createObjectURL(f);});
		document.getElementById('ag-head').addEventListener('input',draw);
		document.getElementById('ag-sub').addEventListener('input',draw);
		document.getElementById('ag-dl').addEventListener('click',function(){canvas.toBlob(function(b){var a=document.createElement('a');a.href=URL.createObjectURL(b);a.download='alliance-visuel.png';document.body.appendChild(a);a.click();a.remove();URL.revokeObjectURL(a.href);},'image/png');});
		document.getElementById('ag-imgshare').addEventListener('click',function(){try{if(navigator.clipboard)navigator.clipboard.writeText(window.AG_CAPTION);}catch(e){}canvas.toBlob(function(b){if(!b)return;var file=new File([b],'alliance-visuel.png',{type:'image/png'});if(navigator.canShare&&navigator.canShare({files:[file]})){navigator.share({files:[file]}).catch(function(){});}else{var a=document.createElement('a');a.href=URL.createObjectURL(b);a.download='alliance-visuel.png';document.body.appendChild(a);a.click();a.remove();}},'image/png');});
		draw();
	})();
	</script>

	<section class="ag-section ag-section--graphite ag-pane ag-pane--image" style="display:none;">
		<div class="ag-container">
			<h2 class="ag-section__title">Visuels prêts à poster 🖼️</h2>
			<p class="ag-section__desc">Déjà brandés avec ton lien : télécharge-les et poste-les direct.</p>
			<div class="ag-vis-grid">
				<?php foreach ( $media as $m ) : ?>
					<div class="ag-vis">
						<img src="<?php echo esc_url( $m ); ?>" alt="visuel Alliance Groupe" loading="lazy">
						<div class="ag-vis__btns">
							<a class="ag-btn-gold" href="<?php echo esc_url( $m ); ?>" download>⬇ Télécharger</a>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section class="ag-section ag-section--graphite">
		<div class="ag-container ag-tools">
			<h2 class="ag-section__title">Idées &amp; scripts 🧰</h2>

			<h3>💡 Idées de vidéos (accroches qui marchent)</h3>
			<ul class="ag-ideas">
				<li>« On m'a dit que mon quartier ne réussit pas. » → puis tu montres la réussite</li>
				<li>« Lui il livre. Moi je vends des sites depuis mon tel. »</li>
				<li>« Pendant que tu dors, ton site vend. Un client peut commander à 3h. »</li>
				<li>« Ce commerce passait à côté de clients à cause de ÇA. » (avant / après)</li>
				<li>« POV : t'as 18 ans et t'as déjà un business. »</li>
				<li>« Le talent est partout, les opportunités non. » (Programme Racines)</li>
			</ul>

			<h3>🔊 Format &amp; son (pour percer)</h3>
			<ul class="ag-ideas ag-ideas--tips">
				<li><strong>Son tendance</strong> : dans TikTok → ➕ → Sons → « Tendances ». Ajoute-le après avoir importé ta vidéo.</li>
				<li><strong>Accroche dans les 2 premières secondes</strong>, sinon les gens scrollent.</li>
				<li><strong>Sous-titres</strong> activés · format <strong>9:16</strong> · poste <strong>1 vidéo/jour</strong>.</li>
				<li>Ton <strong>lien en bio</strong> + en légende (le message tout prêt est dans « Mon compte »).</li>
			</ul>

			<h3>🗣️ Scripts de vente</h3>
			<details><summary>Message d'approche (DM / WhatsApp)</summary><p>Bonjour [Nom], je suis tombé sur votre [commerce/page]. Je travaille avec Alliance Groupe : on fait des sites pros à <strong>prix fixe (dès 490 €)</strong>, livrés en quelques jours, sans rendez-vous. Ça vous dirait un site qui vous ramène des clients 24/7 ? Voici un aperçu 👉 [ton lien]</p></details>
			<details><summary>Objection : « je n'ai pas le budget »</summary><p>Je comprends. Justement c'est un <strong>prix fixe clair</strong> (dès 490 €), sans abonnement caché, et ça travaille pour vous en continu. On peut démarrer petit (one-page) : [ton lien]</p></details>
			<details><summary>Objection : « j'ai déjà un site »</summary><p>Top ! Souvent on peut le moderniser pour qu'il convertisse mieux (mobile, rapidité, Google). Je vous fais un avis rapide ? Nos offres : [ton lien]</p></details>
		</div>
	</section>
</main>

<style>
.ag-esp-head{display:flex;justify-content:space-between;align-items:flex-start;gap:20px;flex-wrap:wrap;}
.ag-esp-logout{flex-shrink:0;}
.ag-mode{display:flex;gap:10px;flex-wrap:wrap;margin-top:18px;}
.ag-mode-btn{padding:12px 18px;border-radius:999px;font-weight:700;font-size:.95rem;color:#fff;background:rgba(255,255,255,.06);border:1px solid rgba(212,180,92,.25);cursor:pointer;transition:transform .2s,background .2s;}
.ag-mode-btn:hover{transform:translateY(-2px);}
.ag-mode-btn.is-active{background:linear-gradient(135deg,#D4B45C,#F37A1F);color:#10100a;border-color:transparent;}
.ag-mode-hint{margin-top:12px;color:var(--color-text-muted);font-size:.88rem;}
.ag-ct{display:flex;gap:10px;margin-top:22px;padding:6px;background:rgba(255,255,255,.04);border:1px solid rgba(212,180,92,.2);border-radius:14px;max-width:420px;}
.ag-ct-btn{flex:1;padding:14px 12px;border:none;background:transparent;color:var(--color-text-soft);font-weight:800;font-size:1rem;border-radius:10px;cursor:pointer;transition:background .2s,color .2s;}
.ag-ct-btn.is-active{background:linear-gradient(135deg,#D4B45C,#F37A1F);color:#10100a;}
.ag-studio-guest{margin-top:18px;padding:15px 20px;background:linear-gradient(135deg,rgba(212,180,92,.14),rgba(243,122,31,.06));border:1px solid rgba(212,180,92,.4);border-radius:14px;color:#fff;font-size:.92rem;line-height:1.6;}
.ag-studio-guest strong{color:#e8c766;}
.ag-studio-guest a{color:var(--color-gold);font-weight:700;}
.ag-vis-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-top:30px;}
.ag-vis{background:rgba(255,255,255,.03);border:1px solid rgba(212,180,92,.18);border-radius:16px;overflow:hidden;display:flex;flex-direction:column;}
.ag-vis img{width:100%;height:auto;display:block;}
.ag-vis__btns{display:flex;flex-direction:column;gap:8px;padding:12px;}
.ag-vis__btns .ag-btn-gold,.ag-vis__btns .ag-btn-outline{text-align:center;}
.ag-maker{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:28px;align-items:start;}
.ag-maker__preview{background:#0e0d11;border:1px solid rgba(212,180,92,.18);border-radius:14px;overflow:hidden;}
.ag-maker__preview canvas{width:100%;height:auto;display:block;}
.ag-maker__ctrl{display:flex;flex-direction:column;gap:16px;}
.ag-maker__ctrl label{display:flex;flex-direction:column;gap:8px;color:var(--color-text-soft);font-size:.9rem;font-weight:600;}
.ag-maker__ctrl input[type=text]{padding:13px 16px;border-radius:12px;border:1px solid rgba(212,180,92,.3);background:rgba(255,255,255,.05);color:#fff;font-size:.95rem;}
.ag-maker__ctrl input[type=file]{color:var(--color-text-soft);font-size:.88rem;}
.ag-maker--v .ag-maker__preview{display:flex;justify-content:center;background:#0e0d11;}
.ag-maker--v .ag-maker__preview canvas{width:auto;max-width:100%;max-height:540px;}
.ag-bgpick{display:flex;flex-direction:column;gap:8px;}
.ag-bgpick__lbl{color:var(--color-text-soft);font-size:.9rem;font-weight:600;}
.ag-bgpick__row{display:flex;gap:8px;flex-wrap:wrap;}
.ag-bgpick__thumb{width:54px;height:72px;border-radius:10px;border:2px solid rgba(212,180,92,.25);background-size:cover;background-position:center;cursor:pointer;padding:0;transition:transform .2s,border-color .2s;}
.ag-bgpick__thumb:hover{transform:translateY(-2px);border-color:rgba(212,180,92,.6);}
.ag-bgpick__thumb.is-active{border-color:#e8c766;box-shadow:0 0 0 2px rgba(232,199,102,.3);}
.ag-fields__lbl{color:var(--color-text-soft);font-size:.9rem;font-weight:600;}
.ag-tinput{padding:12px 16px;border-radius:12px;border:1px solid rgba(212,180,92,.3);background:rgba(255,255,255,.05);color:#fff;font-size:.95rem;}
.ag-opt{display:flex;flex-direction:column;gap:8px;}
.ag-opt__lbl{color:var(--color-text-soft);font-size:.9rem;font-weight:600;}
.ag-seg{display:flex;gap:8px;}
.ag-font-btn{flex:1;padding:11px 8px;border-radius:10px;border:1px solid rgba(212,180,92,.25);background:rgba(255,255,255,.05);color:#fff;font-weight:700;font-size:1rem;cursor:pointer;transition:background .2s,border-color .2s;}
.ag-font-btn.is-active{background:linear-gradient(135deg,#D4B45C,#F37A1F);color:#10100a;border-color:transparent;}
.ag-colors{display:flex;gap:10px;flex-wrap:wrap;}
.ag-color-btn{width:34px;height:34px;border-radius:50%;border:2px solid rgba(255,255,255,.25);cursor:pointer;padding:0;transition:transform .15s,border-color .15s;}
.ag-color-btn:hover{transform:scale(1.1);}
.ag-color-btn.is-active{border-color:#fff;box-shadow:0 0 0 2px rgba(232,199,102,.7);}
.ag-dur{display:flex;flex-direction:column;gap:8px;}
.ag-dur__lbl{color:var(--color-text-soft);font-size:.9rem;font-weight:600;}
.ag-dur__row{display:flex;gap:8px;flex-wrap:wrap;}
.ag-dur-btn{flex:1;min-width:60px;padding:11px 8px;border-radius:10px;border:1px solid rgba(212,180,92,.25);background:rgba(255,255,255,.05);color:#fff;font-weight:700;font-size:.92rem;cursor:pointer;transition:background .2s,border-color .2s;}
.ag-dur-btn.is-active{background:linear-gradient(135deg,#D4B45C,#F37A1F);color:#10100a;border-color:transparent;}
.ag-dur-btn--pro{border-color:rgba(232,199,102,.6);}
.ag-dur__hint{color:var(--color-text-muted);font-size:.82rem;line-height:1.4;}
.ag-dur__hint strong{color:#e8c766;}
.ag-vstatus{margin:6px 0 0;color:var(--color-text-soft);font-size:.9rem;min-height:1.2em;}
.ag-vhint{margin:0;padding:14px 16px;background:rgba(212,180,92,.08);border:1px solid rgba(212,180,92,.22);border-radius:12px;color:var(--color-text-soft);font-size:.88rem;line-height:1.7;}
.ag-vhint strong{color:#e8c766;}
.ag-vshare-btn{display:inline-flex;align-items:center;justify-content:center;gap:10px;}
.ag-vshare-ic{display:inline-flex;gap:5px;}
.ag-vsi{display:inline-block;font-size:1.2em;line-height:1;animation:ag-vsi-bob 1.4s ease-in-out infinite;}
.ag-vsi--snap{animation-delay:.22s;}
.ag-vsi--ig{animation-delay:.44s;}
@keyframes ag-vsi-bob{0%,100%{transform:translateY(0) rotate(0deg);}30%{transform:translateY(-5px) rotate(-9deg);}60%{transform:translateY(1px) rotate(7deg);}}
@media(prefers-reduced-motion:reduce){.ag-vsi{animation:none;}}
.ag-tools h3{color:#fff;font-family:var(--font-serif);font-size:1.2rem;margin:32px 0 12px;}
.ag-ideas{list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:9px;}
.ag-ideas li{position:relative;padding-left:26px;color:var(--color-text-soft);line-height:1.5;}
.ag-ideas li::before{content:'💡';position:absolute;left:0;}
.ag-ideas--tips li::before{content:'✅';}
.ag-tools details{background:rgba(255,255,255,.03);border:1px solid rgba(212,180,92,.16);border-radius:12px;padding:0 18px;margin-bottom:10px;}
.ag-tools details[open]{border-color:rgba(212,180,92,.4);}
.ag-tools summary{cursor:pointer;padding:16px 0;font-weight:700;color:#fff;list-style:none;}
.ag-tools summary::-webkit-details-marker{display:none;}
.ag-tools details p{color:var(--color-text-soft);margin:0 0 16px;line-height:1.6;}
@media(max-width:768px){#ag-main-content.ag-studio > .ag-section:first-child{padding-top:170px;}}
@media(max-width:760px){.ag-vis-grid{grid-template-columns:1fr 1fr;}.ag-maker{grid-template-columns:1fr;}.ag-maker--v .ag-maker__preview canvas{max-height:60vh;}}
@media(max-width:480px){.ag-vis-grid{gap:12px;}.ag-vis__btns{padding:8px;gap:6px;}.ag-vis__btns .ag-btn-gold,.ag-vis__btns .ag-btn-outline{padding:9px 8px;font-size:.8rem;}.ag-btn-gold,.ag-btn-outline{width:100%;justify-content:center;text-align:center;}}
</style>

<?php get_footer(); ?>
