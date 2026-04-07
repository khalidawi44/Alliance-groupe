<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<nav class="ag-nav" id="ag-nav">
    <div class="ag-nav__inner">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="ag-nav__logo" aria-label="Alliance Groupe — Accueil">
            <?php
            $logo_text = 'Alliance Groupe';
            $delay = 0;
            for ($i = 0; $i < mb_strlen($logo_text); $i++) {
                $char = mb_substr($logo_text, $i, 1);
                if ($char === ' ') {
                    echo '&nbsp;';
                } else {
                    echo '<span class="ag-logo-letter" style="--d:' . $delay . '">' . esc_html($char) . '</span>';
                    $delay++;
                }
            }
            ?>
        </a>

        <ul class="ag-nav__list" id="ag-nav-list">
            <li><a href="<?php echo esc_url(home_url('/')); ?>">Accueil</a></li>
            <li><a href="<?php echo esc_url(home_url('/services')); ?>">Services</a></li>
            <li><a href="<?php echo esc_url(home_url('/realisations')); ?>">Réalisations</a></li>
            <li><a href="<?php echo esc_url(home_url('/a-propos')); ?>">À propos</a></li>
            <li><a href="<?php echo esc_url(home_url('/blog')); ?>">Blog</a></li>
            <li><a href="<?php echo esc_url(home_url('/contact')); ?>">Contact</a></li>
        </ul>

        <a href="<?php echo esc_url(home_url('/contact')); ?>" class="ag-nav__cta">
            Parlons-en <span>→</span>
        </a>

        <button class="ag-nav__burger" id="ag-burger" aria-label="Menu">
            <span></span><span></span><span></span>
        </button>
    </div>
</nav>
