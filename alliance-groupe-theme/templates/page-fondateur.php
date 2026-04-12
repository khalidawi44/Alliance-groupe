<?php
/**
 * Template Name: Notre Fondateur
 */
get_header();
$img_base = get_stylesheet_directory_uri() . '/assets/images/team/';
$img_url = '';
$img_dir = get_stylesheet_directory() . '/assets/images/team/';
foreach (array('jpg','jpeg','png','webp') as $ext) {
    if (file_exists($img_dir . 'fabrizio.' . $ext)) {
        $img_url = $img_base . 'fabrizio.' . $ext;
        break;
    }
}
?>

<main>
    <!-- Hero -->
    <section class="ag-hero" style="min-height:70vh;">
        <div class="ag-hero__bg">
            <div class="ag-hero__orb ag-hero__orb--1"></div>
        </div>
        <div class="ag-hero__content">
            <span class="ag-tag">Notre fondateur</span>
            <h1 class="ag-hero__title">
                <span class="ag-line"><em>Fabrizio</em></span>
                <span class="ag-line">L'homme qui a tout changé</span>
                <span class="ag-line">avec un ordinateur</span>
            </h1>
        </div>
    </section>

    <!-- Histoire -->
    <section class="ag-section ag-section--marbre">
        <div class="ag-container ag-container--narrow">

            <?php if ($img_url) : ?>
            <div style="text-align:center;margin-bottom:48px;">
                <img src="<?php echo esc_url($img_url); ?>" alt="Fabrizio — Fondateur Alliance Groupe"
                     style="width:280px;height:350px;object-fit:cover;border-radius:20px;border:2px solid rgba(212,180,92,.2);">
            </div>
            <?php endif; ?>

            <div class="ag-fondateur-story">

                <h2>Les ruelles de Naples</h2>

                <p>Il y a des histoires qui commencent dans des bureaux climatisés, avec des business plans et des investisseurs. Celle de Fabrizio commence dans les ruelles étroites des <strong>Quartieri Spagnoli</strong>, à Naples — là où le linge sèche entre les immeubles et où les enfants jouent sur des pavés centenaires.</p>

                <p>Fabrizio avait 19 ans quand il a compris quelque chose qui allait changer sa vie : <strong>le numérique pouvait sortir les gens de la pauvreté</strong>. Pas avec de grands discours. Pas avec de l'argent qu'il n'avait pas. Mais avec un vieil ordinateur, une connexion internet instable, et une patience infinie.</p>

                <h2>Un ordinateur pour dix familles</h2>

                <p>Tout a commencé dans l'arrière-salle d'une église de quartier. Un curé qui voulait bien prêter l'espace. Un ordinateur récupéré. Et Fabrizio, qui passait ses soirées à apprendre aux gens ce que personne ne leur avait jamais appris.</p>

                <blockquote>"Il y avait Maria, 63 ans, qui ne savait pas ce qu'était un email. En deux mois, elle vendait ses conserves de tomates sur internet. Quand elle a reçu sa première commande d'un client à Milan, elle a pleuré. Moi aussi."</blockquote>

                <p>Puis il y a eu Giuseppe, ancien maçon, dos cassé par trente ans de chantiers. Plus capable de travailler physiquement. Fabrizio lui a appris à créer un site web pour proposer ses services de consultant en rénovation. <strong>En six mois, Giuseppe gagnait plus qu'en portant des parpaings.</strong></p>

                <p>Et Rosa. Rosa qui élevait seule ses trois enfants dans un appartement de 35m². Qui n'avait jamais touché un clavier. Fabrizio l'a formée au secrétariat numérique. Aujourd'hui, Rosa travaille à distance pour deux cabinets d'avocats. <strong>Ses enfants n'ont plus jamais eu faim.</strong></p>

                <h2>De dix à trois cents</h2>

                <p>Le bouche-à-oreille a fait le reste. En un an, ce n'était plus un ordinateur dans une église — c'étaient <strong>trois salles, douze ordinateurs, et des files d'attente dans le couloir</strong>. Des mères de famille, des jeunes décrocheurs, des retraités, des immigrés qui ne parlaient pas encore italien. Tous venaient avec la même chose dans les yeux : <strong>l'espoir.</strong></p>

                <p>Fabrizio ne comptait pas ses heures. Il apprenait le jour pour enseigner le soir. Le design, le code, le marketing digital, le référencement — tout ce qu'il pouvait transformer en outil de survie pour ces familles.</p>

                <blockquote>"Je n'ai jamais voulu faire de la charité. La charité, ça maintient les gens dans la dépendance. Moi je voulais leur donner un <strong>métier</strong>. Une compétence que personne ne pourrait jamais leur reprendre."</blockquote>

                <h2>Le prix à payer</h2>

                <p>Mais cette mission avait un coût. Fabrizio ne gagnait rien. Il vivait chez sa mère, mangeait des pâtes al pomodoro tous les soirs, et réparait des téléphones pour payer la connexion internet des salles de cours.</p>

                <p>Ses amis partaient à Londres, à Berlin, à New York. Ils lui disaient : <em>"Arrête de perdre ton temps avec ces gens. Viens, il y a de l'argent à faire."</em></p>

                <p>Il restait.</p>

                <p>Parce que chaque semaine, il voyait un visage s'illuminer. Un père de famille qui décrochait son premier contrat en freelance. Une grand-mère qui envoyait un message à son petit-fils émigré en Allemagne. <strong>Un gamin de 16 ans qui créait son premier site web et disait : "Je veux faire ça toute ma vie."</strong></p>

                <h2>La naissance d'Alliance Groupe</h2>

                <p>C'est de là qu'est née Alliance Groupe. Pas dans un incubateur de startups. Pas avec des millions d'euros de levée de fonds. <strong>Dans les quartiers pauvres de Naples, avec des gens qui n'avaient rien — sauf la volonté de s'en sortir.</strong></p>

                <p>Fabrizio a compris que ce qu'il faisait pour les familles napolitaines, il pouvait le faire pour les entreprises du monde entier. Créer des sites web qui génèrent des revenus. Automatiser des process pour gagner du temps. Utiliser l'IA pour donner aux petits les armes des grands.</p>

                <h2>Nantes, 2009 — Un nouveau chapitre</h2>

                <p>En 2009, à 27 ans, Fabrizio prend une décision qui changera tout : <strong>il s'installe à Nantes</strong>. La France, un nouveau pays, une nouvelle langue, une nouvelle vie. Mais la même mission.</p>

                <p>Naples restera toujours dans son cœur — les cours du soir continuent là-bas grâce à l'équipe locale. Mais c'est à Nantes que Fabrizio va donner à Alliance Groupe la dimension internationale qu'il a toujours rêvée. La Bretagne et les Pays de la Loire deviennent son nouveau terrain de jeu. Les artisans, les commerçants, les PME de l'Ouest — tous découvrent qu'un Napolitain passionné peut transformer leur business.</p>

                <blockquote>"Quand je suis arrivé à Nantes, je ne parlais presque pas français. Mon premier client m'a fait confiance parce qu'il a vu mes résultats à Naples. Il m'a dit : 'Je me fiche de ton accent, je veux les mêmes chiffres.' Six mois plus tard, ses devis avaient triplé."</blockquote>

                <p>Aujourd'hui, Fabrizio vit et travaille à <strong>Nantes</strong>, où se trouve le siège d'Alliance Groupe. L'agence a aussi des bureaux à <strong>Naples</strong> et <strong>Marrakech</strong>. Une équipe de sept personnes passionnées. Des dizaines de clients satisfaits.</p>

                <p>Mais Fabrizio n'a jamais arrêté les cours du soir à Naples. <strong>Chaque mardi et jeudi, de 19h à 22h</strong>, l'équipe napolitaine est dans cette même salle — avec de nouveaux ordinateurs, certes, mais la même mission.</p>

                <blockquote>"Les gens me demandent pourquoi je continue. La réponse est simple : parce que chaque personne qui apprend à utiliser le numérique, c'est une famille qui mange mieux, un enfant qui rêve plus grand, un quartier qui se relève. <strong>Comment je pourrais arrêter ?</strong>"</blockquote>

                <h2>Sa vision</h2>

                <p>Pour Fabrizio, Alliance Groupe n'est pas juste une agence web. C'est la preuve vivante que <strong>le numérique peut changer des vies</strong>. Que la technologie n'est pas réservée aux privilégiés. Que chaque entrepreneur, chaque artisan, chaque commerçant mérite d'exister en ligne — et de prospérer.</p>

                <p><strong>C'est cette conviction qui guide chaque projet, chaque pixel, chaque ligne de code que nous écrivons chez Alliance Groupe.</strong></p>

            </div>

            <!-- CTA -->
            <div style="text-align:center;margin-top:60px;padding-top:40px;border-top:1px solid rgba(255,255,255,.06);">
                <h3 style="font-size:1.6rem;margin-bottom:16px;">Vous avez un projet ?<br><em>Fabrizio et son équipe sont là.</em></h3>
                <p style="color:#b0b0bc;margin-bottom:28px;">Le même engagement, la même passion — au service de votre réussite.</p>
                <div class="ag-hero__buttons">
                    <a href="tel:+33623526074" class="ag-btn-gold">Appeler Fabrizio — 06.23.52.60.74</a>
                    <a href="<?php echo esc_url(home_url('/contact')); ?>" class="ag-btn-outline">Nous contacter →</a>
                </div>
            </div>

        </div>
    </section>
</main>

<?php get_footer(); ?>
