<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
    <style>
        /* ── Reset & Base ── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0a0a0a;
            color: #f0f0f0;
            line-height: 1.6;
        }
        a { color: #D4B45C; text-decoration: none; }
        a:hover { text-decoration: underline; }
        img { max-width: 100%; height: auto; }

        /* ── Header ── */
        .site-header {
            text-align: center;
            padding: 1.5rem 1rem;
            border-bottom: 1px solid #222;
        }
        .site-header .site-name {
            font-size: 1.4rem;
            color: #D4B45C;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        /* ── Hero ── */
        .hero {
            text-align: center;
            padding: 6rem 1.5rem;
            background: linear-gradient(180deg, #111 0%, #0a0a0a 100%);
        }
        .hero h1 {
            font-size: 2.8rem;
            color: #fff;
            margin-bottom: 0.5rem;
        }
        .hero h1 span { color: #D4B45C; }
        .hero p.subtitle {
            font-size: 1.15rem;
            color: #aaa;
            margin-bottom: 2rem;
            font-style: italic;
        }
        .hero .btn {
            display: inline-block;
            padding: 0.75rem 2rem;
            background: #D4B45C;
            color: #0a0a0a;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-radius: 2px;
            transition: background 0.2s;
        }
        .hero .btn:hover { background: #c2a24e; text-decoration: none; }

        /* ── Cards ── */
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            max-width: 1000px;
            margin: 0 auto;
            padding: 4rem 1.5rem;
        }
        .card {
            background: #141414;
            border: 1px solid #222;
            border-radius: 4px;
            padding: 2.5rem 2rem;
            text-align: center;
        }
        .card h2 {
            font-size: 1.4rem;
            color: #D4B45C;
            margin-bottom: 0.75rem;
        }
        .card p { color: #bbb; font-size: 0.95rem; }

        /* ── Info Section ── */
        .info-section {
            max-width: 1000px;
            margin: 0 auto;
            padding: 3rem 1.5rem;
            text-align: center;
            border-top: 1px solid #1a1a1a;
        }
        .info-section h2 {
            font-size: 1.8rem;
            color: #D4B45C;
            margin-bottom: 1rem;
        }
        .info-section p { color: #bbb; max-width: 600px; margin: 0 auto 1rem; }

        /* ── Footer ── */
        .site-footer {
            border-top: 1px solid #222;
            padding: 3rem 1.5rem;
            background: #080808;
        }
        .footer-inner {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 2rem;
            max-width: 1000px;
            margin: 0 auto;
        }
        .footer-col h3 {
            color: #D4B45C;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 0.75rem;
        }
        .footer-col p, .footer-col li {
            color: #888;
            font-size: 0.9rem;
            line-height: 1.8;
        }
        .footer-col ul { list-style: none; }
        .footer-bottom {
            text-align: center;
            padding: 1.5rem 1rem 1rem;
            color: #555;
            font-size: 0.8rem;
            max-width: 1000px;
            margin: 0 auto;
        }
        .footer-bottom a { color: #D4B45C; }

        /* ── Responsive ── */
        @media (max-width: 600px) {
            .hero h1 { font-size: 1.8rem; }
            .hero { padding: 4rem 1rem; }
            .cards { padding: 2rem 1rem; }
        }
    </style>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- Header -->
<header class="site-header">
    <div class="site-name"><?php bloginfo( 'name' ); ?></div>
</header>

<!-- Hero -->
<section class="hero">
    <h1>Bienvenue chez <span>[Votre Restaurant]</span></h1>
    <p class="subtitle">Cuisine authentique depuis [ann&eacute;e] &mdash; [Votre Ville]</p>
    <a href="#carte" class="btn">D&eacute;couvrir la carte</a>
</section>

<!-- Cards -->
<section class="cards" id="carte">
    <div class="card">
        <h2>Notre Carte</h2>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse varius enim in eros elementum tristique. D&eacute;couvrez nos entr&eacute;es, plats et desserts.</p>
    </div>
    <div class="card">
        <h2>R&eacute;servation</h2>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. R&eacute;servez votre table en ligne ou par t&eacute;l&eacute;phone au [000-000-0000].</p>
    </div>
    <div class="card">
        <h2>&Agrave; propos</h2>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Notre &eacute;quipe vous accueille dans un cadre chaleureux au c&oelig;ur de [Votre Ville].</p>
    </div>
</section>

<!-- Info -->
<section class="info-section">
    <h2>Notre histoire</h2>
    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent commodo cursus magna, vel scelerisque nisl consectetur et. Cras mattis consectetur purus sit amet fermentum.</p>
    <p>Donec sed odio dui. Nulla vitae elit libero, a pharetra augue. Nullam id dolor id nibh ultricies vehicula ut id elit.</p>
</section>

<!-- Footer -->
<footer class="site-footer">
    <div class="footer-inner">
        <div class="footer-col">
            <h3>Adresse</h3>
            <p>
                [Votre Restaurant]<br>
                123 rue Placeholder<br>
                [Votre Ville], QC H0H 0H0
            </p>
        </div>
        <div class="footer-col">
            <h3>Horaires</h3>
            <ul>
                <li>Lundi &ndash; Vendredi : 11h &ndash; 22h</li>
                <li>Samedi : 10h &ndash; 23h</li>
                <li>Dimanche : 10h &ndash; 21h</li>
            </ul>
        </div>
        <div class="footer-col">
            <h3>Contact</h3>
            <p>
                T&eacute;l : [000-000-0000]<br>
                Courriel : info@votrerestaurant.com
            </p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?php echo date( 'Y' ); ?> [Votre Restaurant]. Tous droits r&eacute;serv&eacute;s.</p>
        <p>Th&egrave;me gratuit par <a href="https://alliancegroupe-inc.com" target="_blank" rel="noopener">Alliance Groupe</a></p>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
