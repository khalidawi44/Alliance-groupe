<section class="ag-marquee">
    <div class="ag-marquee__track">
        <?php
        $services = [
            'Création Web',
            'IA & Automatisation',
            'SEO',
            'Publicité Digitale',
            'Branding',
            'Conseil Stratégique',
            'Création Web',
            'IA & Automatisation',
            'SEO',
            'Publicité Digitale',
            'Branding',
            'Conseil Stratégique',
        ];
        foreach ($services as $s) :
        ?>
        <span class="ag-marquee__item"><?php echo esc_html($s); ?></span>
        <?php endforeach; ?>
    </div>
</section>
