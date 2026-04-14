<?php
/**
 * Shared download modal + lead capture JS.
 *
 * Used on /templates-wordpress (hub) and on the 4 profession
 * pages (via template-parts/metier-page.php).
 *
 * @package Alliance_Groupe
 */
?>

<div class="ag-dl-modal" id="ag-dl-modal">
    <div class="ag-dl-modal__overlay" id="ag-dl-modal-close"></div>
    <div class="ag-dl-modal__box">
        <button type="button" class="ag-dl-modal__close" id="ag-dl-modal-x">✕</button>
        <div class="ag-dl-modal__icon">🎁</div>
        <h3 class="ag-dl-modal__title">Votre téléchargement <em>gratuit</em></h3>
        <p class="ag-dl-modal__desc">Entrez vos coordonnées pour recevoir le lien de téléchargement. Pas de spam, promis.</p>
        <form class="ag-dl-modal__form" id="ag-dl-form">
            <input type="hidden" id="ag-dl-file" value="">
            <input type="hidden" id="ag-dl-template" value="">
            <div class="ag-form__group">
                <input type="text" id="ag-dl-name" placeholder="Votre nom" required>
            </div>
            <div class="ag-form__group">
                <input type="email" id="ag-dl-email" placeholder="Votre email" required>
            </div>
            <div class="ag-form__group">
                <input type="tel" id="ag-dl-phone" placeholder="Votre téléphone (optionnel)">
            </div>
            <button type="submit" class="ag-btn-gold" style="width:100%;justify-content:center;">Télécharger maintenant →</button>
        </form>
        <p class="ag-dl-modal__trust">🔒 Vos données sont protégées. Pas de spam.</p>
    </div>
</div>

<script>
(function(){
    var modal = document.getElementById('ag-dl-modal');
    if (!modal || modal.dataset.bound === '1') return;
    modal.dataset.bound = '1';
    var form = document.getElementById('ag-dl-form');
    var fileInput = document.getElementById('ag-dl-file');
    var tplInput = document.getElementById('ag-dl-template');

    document.querySelectorAll('.ag-dl-trigger').forEach(function(btn){
        btn.addEventListener('click', function(){
            fileInput.value = btn.getAttribute('data-file');
            tplInput.value = btn.getAttribute('data-template');
            modal.classList.add('open');
            document.body.style.overflow = 'hidden';
        });
    });

    function closeModal(){
        modal.classList.remove('open');
        document.body.style.overflow = '';
    }
    var closeOverlay = document.getElementById('ag-dl-modal-close');
    var closeX = document.getElementById('ag-dl-modal-x');
    if (closeOverlay) closeOverlay.addEventListener('click', closeModal);
    if (closeX) closeX.addEventListener('click', closeModal);

    form.addEventListener('submit', function(e){
        e.preventDefault();
        var name  = document.getElementById('ag-dl-name').value;
        var email = document.getElementById('ag-dl-email').value;
        var phone = document.getElementById('ag-dl-phone').value;
        var template = tplInput.value;
        var file = fileInput.value;

        var data = new FormData();
        data.append('action', 'ag_save_lead');
        data.append('name', name);
        data.append('email', email);
        data.append('phone', phone);
        data.append('template', template);
        data.append('ag_lead_nonce', '<?php echo wp_create_nonce( "ag_lead_nonce" ); ?>');

        fetch('<?php echo esc_url( admin_url( "admin-ajax.php" ) ); ?>', {
            method: 'POST',
            body: data
        }).then(function(){
            var link = document.createElement('a');
            link.href = file;
            link.download = '';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            form.innerHTML = '<div style="text-align:center;padding:20px 0;"><div style="font-size:3rem;margin-bottom:12px;">✅</div><h3 style="margin-bottom:8px;">Merci ' + name + ' !</h3><p style="color:#b0b0bc;">Le téléchargement a démarré. Besoin d\'aide pour l\'installation ?</p><a href="tel:+33623526074" class="ag-btn-gold" style="margin-top:16px;">Appeler Fabrizio — 06.23.52.60.74</a></div>';
        }).catch(function(){
            // Fallback : still trigger download even if AJAX fails
            var link = document.createElement('a');
            link.href = file;
            link.download = '';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    });
})();
</script>
