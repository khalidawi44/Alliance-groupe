<?php
/**
 * Barre de navigation de l'espace membre (commercial).
 * Sépare clairement les deux univers : « Mon compte » (ventes, commissions)
 * et « Studio » (création de contenu), + raccourci Classement.
 *
 * Usage : get_template_part( 'template-parts/espace-nav', null, array( 'active' => 'compte' ) );
 * Clés actives possibles : compte | studio | classement
 */
$ag_active = isset( $args['active'] ) ? $args['active'] : '';
$ag_items  = array(
	'compte'     => array( '💼', 'Mon compte', 'Ventes &amp; commissions', home_url( '/espace-ambassadeur' ) ),
	'studio'     => array( '🎬', 'Studio',      'Créer &amp; partager',     home_url( '/studio' ) ),
	'classement' => array( '🏆', 'Classement',  'Le championnat',           home_url( '/classement' ) ),
);
?>
<div class="ag-espnav-wrap">
	<nav class="ag-espnav" aria-label="Navigation de l'espace membre">
		<?php foreach ( $ag_items as $k => $it ) : ?>
			<a class="ag-espnav__item<?php echo $k === $ag_active ? ' is-active' : ''; ?>" href="<?php echo esc_url( $it[3] ); ?>"<?php echo $k === $ag_active ? ' aria-current="page"' : ''; ?>>
				<span class="ag-espnav__ic"><?php echo esc_html( $it[0] ); ?></span>
				<span class="ag-espnav__txt"><strong><?php echo esc_html( $it[1] ); ?></strong><small><?php echo wp_kses_post( $it[2] ); ?></small></span>
			</a>
		<?php endforeach; ?>
	</nav>
</div>
<style>
.ag-espnav-wrap{margin:26px 0 4px;}
.ag-espnav{display:flex;gap:12px;flex-wrap:wrap;}
.ag-espnav__item{display:flex;align-items:center;gap:12px;flex:1;min-width:170px;padding:14px 18px;border-radius:14px;background:rgba(255,255,255,.03);border:1px solid rgba(212,180,92,.18);text-decoration:none;transition:transform .2s,border-color .2s,background .2s;}
.ag-espnav__item:hover{transform:translateY(-2px);border-color:rgba(212,180,92,.45);text-decoration:none;}
.ag-espnav__ic{font-size:1.5rem;line-height:1;}
.ag-espnav__txt{display:flex;flex-direction:column;line-height:1.25;}
.ag-espnav__txt strong{color:#fff;font-size:1rem;}
.ag-espnav__txt small{color:var(--color-text-soft);font-size:.78rem;}
.ag-espnav__item.is-active{background:linear-gradient(160deg,rgba(212,180,92,.2),rgba(243,122,31,.07));border-color:rgba(212,180,92,.6);}
.ag-espnav__item.is-active .ag-espnav__txt strong{color:#e8c766;}
@media(max-width:760px){.ag-espnav__item{min-width:calc(50% - 6px);flex:1 1 calc(50% - 6px);}.ag-espnav__item:last-child{min-width:100%;}}
</style>
