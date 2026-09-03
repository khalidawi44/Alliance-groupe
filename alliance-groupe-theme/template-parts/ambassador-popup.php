<?php
/**
 * Pop-up d'incitation : devenir ambassadeur (gagner de l'argent).
 * Affiché une fois par session aux visiteurs non-membres (via wp_footer).
 */
?>
<div class="ag-amb-pop" id="ag-amb-pop" aria-hidden="true">
	<div class="ag-amb-pop__overlay" data-close="1"></div>
	<div class="ag-amb-pop__box" role="dialog" aria-label="Devenir ambassadeur">
		<button type="button" class="ag-amb-pop__x" data-close="1" aria-label="Fermer"></button>
		<div class="ag-amb-pop__emoji"></div>
		<h3 class="ag-amb-pop__title">Gagne de l'argent avec <em>Alliance Groupe</em></h3>
		<p class="ag-amb-pop__txt">Deviens <strong>ambassadeur</strong> : touche <strong>10 % sur chaque site vendu</strong>, depuis ton téléphone. Outils de création fournis, lien de vente prêt — <strong>gratuit, sans engagement</strong>.</p>
		<div class="ag-amb-pop__cta">
			<a href="<?php echo esc_url( home_url( '/ambassadeurs' ) ); ?>" class="ag-btn-gold">Je veux gagner →</a>
			<button type="button" class="ag-amb-pop__later" data-close="1">Plus tard</button>
		</div>
	</div>
</div>
<style>
.ag-amb-pop{position:fixed;inset:0;z-index:100002;display:none;align-items:center;justify-content:center;padding:18px;}
.ag-amb-pop.is-open{display:flex;}
.ag-amb-pop__overlay{position:absolute;inset:0;background:rgba(6,6,12,.78);backdrop-filter:blur(4px);}
.ag-amb-pop__box{position:relative;z-index:1;max-width:420px;width:100%;background:linear-gradient(165deg,#16131c,#0e0d13);border:1px solid rgba(212,180,92,.5);border-radius:20px;padding:30px 26px;text-align:center;box-shadow:0 24px 70px rgba(0,0,0,.6);animation:ag-amb-pop-in .35s cubic-bezier(.16,1,.3,1);}
@keyframes ag-amb-pop-in{from{opacity:0;transform:translateY(20px) scale(.96);}to{opacity:1;transform:none;}}
.ag-amb-pop__x{position:absolute;top:12px;right:14px;background:none;border:none;color:var(--color-text-muted);font-size:1.2rem;cursor:pointer;line-height:1;}
.ag-amb-pop__emoji{font-size:2.6rem;}
.ag-amb-pop__title{font-family:var(--font-serif);color:#fff;font-size:1.5rem;margin:8px 0 10px;line-height:1.25;}
.ag-amb-pop__title em{color:#e8c766;font-style:normal;}
.ag-amb-pop__txt{color:var(--color-text-soft);font-size:.97rem;line-height:1.6;margin:0 0 22px;}
.ag-amb-pop__txt strong{color:#fff;}
.ag-amb-pop__cta{display:flex;flex-direction:column;gap:10px;align-items:center;}
.ag-amb-pop__cta .ag-btn-gold{width:100%;justify-content:center;}
.ag-amb-pop__later{background:none;border:none;color:var(--color-text-muted);font-size:.9rem;cursor:pointer;text-decoration:underline;text-underline-offset:3px;}
</style>
<script>
(function(){
	var pop=document.getElementById('ag-amb-pop'); if(!pop) return;
	var KEY='ag_amb_pop_seen';
	try{ if(sessionStorage.getItem(KEY)) return; }catch(e){}
	var opened=false;
	function open(){ if(opened) return; opened=true; pop.classList.add('is-open'); pop.setAttribute('aria-hidden','false'); try{ sessionStorage.setItem(KEY,'1'); }catch(e){} }
	function close(){ pop.classList.remove('is-open'); pop.setAttribute('aria-hidden','true'); }
	pop.querySelectorAll('[data-close]').forEach(function(b){ b.addEventListener('click',close); });
	document.addEventListener('keydown',function(e){ if(e.key==='Escape') close(); });
	var t=setTimeout(open,14000);
	function onScroll(){ if((window.scrollY+window.innerHeight)/document.body.scrollHeight>0.5){ clearTimeout(t); open(); window.removeEventListener('scroll',onScroll); } }
	window.addEventListener('scroll',onScroll,{passive:true});
})();
</script>
