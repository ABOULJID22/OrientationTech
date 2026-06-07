<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Politique de confidentialité — {{ config('app.name', 'OrientationTech') }}</title>
  @include('layouts.theme-init')
  @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
    @vite(['resources/css/app.css', 'resources/js/app.js'])
  @endif
  @include('layouts.favicon')
</head>

@php
    $settings = $siteSettings ?? null;
    $platformName = config('app.name', 'OrientationTech');
    $contactEmail = $settings?->email ?? 'contact@Orientationtech.ma';
    $phone = $settings?->phone ?? '+212 71549452';
    $address = $settings?->address ?? 'Agadir, 85000 Tiznit, Maroc';
@endphp

<body class="bg-[#eff6ff] text-slate-900 dark:bg-slate-950 dark:text-slate-100">
  @include('layouts.navbar')

  <main class="mx-auto max-w-5xl px-4 py-16 sm:px-6 sm:py-24">
    <section class="rounded-[2rem] bg-white p-6 shadow-xl shadow-blue-950/5 ring-1 ring-blue-100 dark:bg-slate-900 dark:ring-white/10 sm:p-10">
      <p class="text-xs font-black uppercase tracking-[0.24em] text-[#2563eb] dark:text-blue-300">Données personnelles</p>
      <h1 class="mt-3 text-3xl font-black tracking-tight text-slate-950 dark:text-white sm:text-4xl">Politique de confidentialité</h1>
      <p class="mt-4 max-w-3xl text-sm leading-7 text-slate-600 dark:text-slate-300">
        Cette politique explique comment {{ $platformName }} collecte, utilise, protège et conserve les données personnelles dans le cadre d’une plateforme éducative d’orientation vers les métiers du numérique.
      </p>

      <div class="prose prose-slate mt-10 max-w-none dark:prose-invert prose-a:text-[#2563eb]">
        <h2>1. Responsable du traitement</h2>
        <p>
          Le responsable du traitement est <strong>{{ $platformName }}</strong>, plateforme d’orientation scolaire et professionnelle assistée par intelligence artificielle.
        </p>
        <ul>
          <li><strong>Adresse :</strong> {{ $address }}</li>
          <li><strong>Email de contact :</strong> <a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a></li>
          <li><strong>Téléphone :</strong> {{ $phone }}</li>
        </ul>

        <h2>2. Cadre de conformité</h2>
        <p>
          {{ $platformName }} s’inscrit dans le respect de la <strong>loi marocaine n° 09-08</strong> relative à la protection des personnes physiques à l’égard du traitement des données à caractère personnel. Les traitements soumis à formalité auprès de la <a href="https://www.cndp.ma/" target="_blank" rel="noopener">CNDP</a> sont déclarés ou autorisés lorsque la réglementation l’exige.
        </p>
        <p>
          Pour les utilisateurs concernés par un cadre international, notamment le RGPD, la plateforme applique les principes de transparence, finalité déterminée, minimisation, exactitude, sécurité, durée limitée de conservation et respect des droits des personnes.
        </p>

        <h2>3. Données collectées</h2>
        <p>Selon votre rôle et votre utilisation, nous pouvons traiter les catégories suivantes :</p>
        <ul>
          <li><strong>Données de compte :</strong> nom, email, mot de passe chiffré, rôle, statut du compte, photo de profil, téléphone, ville, pays.</li>
          <li><strong>Données scolaires :</strong> niveau scolaire, filière, matières préférées, établissement, objectifs, ambitions, environnement familial et scolaire lorsque renseigné.</li>
          <li><strong>Réponses aux tests :</strong> diagnostic académique, questionnaire Ikigaï, test personnalisé, centres d’intérêt, motivations, compétences perçues, traits de personnalité.</li>
          <li><strong>Résultats d’orientation :</strong> scores, domaines recommandés, métiers, parcours de formation, rapports PDF, historique de progression.</li>
          <li><strong>Interactions :</strong> likes, favoris, commentaires, réponses, signalements, notes, vues des articles, ressources et domaines.</li>
          <li><strong>Contenus enseignants :</strong> articles, ressources pédagogiques, fichiers, commentaires et statistiques associées.</li>
          <li><strong>Données techniques :</strong> adresse IP, identifiant de session, logs de connexion, appareil, navigateur, horodatage, événements de sécurité.</li>
          <li><strong>Support et contact :</strong> messages envoyés via formulaire, demandes d’assistance, échanges avec l’administration.</li>
        </ul>

        <h2>4. Finalités du traitement</h2>
        <p>Les données sont utilisées pour :</p>
        <ul>
          <li>créer et sécuriser les comptes utilisateurs ;</li>
          <li>personnaliser le parcours d’orientation de chaque élève ;</li>
          <li>analyser les résultats des tests et générer des recommandations pédagogiques ;</li>
          <li>produire des rapports d’orientation et exports PDF ;</li>
          <li>afficher des tableaux de bord pour élèves, enseignants et administrateurs ;</li>
          <li>permettre les commentaires, likes, favoris, évaluations et signalements ;</li>
          <li>modérer les contenus et protéger les utilisateurs ;</li>
          <li>envoyer des notifications liées au parcours, aux rapports, aux ressources ou aux interactions ;</li>
          <li>améliorer la qualité pédagogique, la sécurité et la fiabilité de la plateforme ;</li>
          <li>répondre aux demandes de contact ou de support.</li>
        </ul>

        <h2>5. Utilisation de l’intelligence artificielle</h2>
        <p>
          Certaines fonctionnalités utilisent une analyse algorithmique ou une assistance IA pour détecter des forces, points d’amélioration, compatibilités avec des domaines numériques, métiers ou parcours de formation.
        </p>
        <p>
          Les résultats IA sont explicatifs et pédagogiques. Ils ne constituent pas une décision automatisée produisant à elle seule un effet juridique ou administratif sur l’élève. Une intervention humaine, familiale ou éducative reste recommandée pour toute décision importante.
        </p>

        <h2>6. Données des mineurs</h2>
        <p>
          La plateforme étant destinée aux élèves, certaines données peuvent concerner des mineurs. {{ $platformName }} veille à limiter la collecte aux informations utiles à l’orientation et à présenter les résultats de manière bienveillante, compréhensible et non stigmatisante.
        </p>
        <p>
          Les parents, tuteurs ou représentants légaux peuvent contacter la plateforme pour toute demande relative au compte ou aux données d’un élève mineur, sous réserve de vérification appropriée.
        </p>

        <h2>7. Base légale</h2>
        <p>Selon les situations, les traitements reposent sur :</p>
        <ul>
          <li>l’exécution du service demandé par l’utilisateur ;</li>
          <li>le consentement lorsque requis, notamment pour certaines fonctionnalités facultatives ;</li>
          <li>l’intérêt légitime de sécuriser, administrer et améliorer la plateforme ;</li>
          <li>le respect d’obligations légales ou réglementaires applicables.</li>
        </ul>

        <h2>8. Destinataires des données</h2>
        <p>
          Les données sont accessibles uniquement aux personnes habilitées selon leur rôle : l’élève pour ses propres informations, les enseignants pour les contenus et interactions qui les concernent, et les administrateurs pour la gestion, la sécurité, la modération et le support.
        </p>
        <p>
          Des prestataires techniques peuvent intervenir pour l’hébergement, l’envoi d’emails, la génération de fichiers, les sauvegardes ou l’analyse IA. Ils ne doivent traiter les données que pour les besoins de la plateforme.
        </p>

        <h2>9. Transferts internationaux</h2>
        <p>
          Certains services techniques ou IA peuvent impliquer un traitement ou un hébergement hors du Maroc. Lorsque cela est nécessaire, {{ $platformName }} veille à appliquer les garanties appropriées et à respecter les formalités applicables, notamment celles prévues par la loi 09-08 pour les transferts de données personnelles à l’étranger.
        </p>

        <h2>10. Durées de conservation</h2>
        <p>Les durées ci-dessous sont indicatives et peuvent être adaptées selon les obligations légales, la sécurité ou la demande de l’utilisateur :</p>
        <table>
          <thead>
            <tr>
              <th>Catégorie</th>
              <th>Durée indicative</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Compte utilisateur</td>
              <td>Pendant la durée d’utilisation du compte, puis archivage ou suppression selon demande et obligations applicables.</td>
            </tr>
            <tr>
              <td>Résultats de tests et rapports</td>
              <td>Pendant le parcours d’orientation de l’élève, afin de permettre le suivi pédagogique.</td>
            </tr>
            <tr>
              <td>Commentaires et interactions</td>
              <td>Pendant la publication du contenu concerné, sauf suppression, modération ou obligation de conservation.</td>
            </tr>
            <tr>
              <td>Logs de sécurité</td>
              <td>Durée limitée nécessaire à la sécurité, à la prévention des abus et au diagnostic technique.</td>
            </tr>
            <tr>
              <td>Messages de contact/support</td>
              <td>Durée nécessaire au traitement de la demande et au suivi administratif.</td>
            </tr>
          </tbody>
        </table>

        <h2>11. Sécurité</h2>
        <p>
          {{ $platformName }} met en œuvre des mesures raisonnables de sécurité : authentification, rôles et permissions, contrôle d’accès, stockage encadré des fichiers, journalisation, limitation des accès administratifs et protection des formulaires.
        </p>
        <p>
          Aucun système n’étant totalement exempt de risque, l’utilisateur doit protéger ses identifiants, utiliser un mot de passe solide et signaler toute activité suspecte.
        </p>

        <h2>12. Cookies et sessions</h2>
        <p>
          La plateforme utilise des cookies ou technologies similaires nécessaires au fonctionnement du site : session, sécurité, authentification, langue, préférence d’affichage et protection contre les abus. Les cookies non strictement nécessaires seront soumis au consentement lorsque requis.
        </p>

        <h2>13. Vos droits</h2>
        <p>
          Conformément au droit marocain applicable, notamment la loi 09-08, vous pouvez demander l’accès, la rectification et, selon les cas, l’opposition au traitement de vos données personnelles.
        </p>
        <p>
          Lorsque le RGPD s’applique, vous pouvez également bénéficier de droits complémentaires : effacement, limitation, portabilité et opposition pour motifs légitimes.
        </p>
        <p>
          Pour exercer vos droits, écrivez à <a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a>. Une vérification d’identité ou de qualité de représentant légal peut être demandée avant traitement.
        </p>

        <h2>14. Réclamations</h2>
        <p>
          Si vous estimez que vos droits ne sont pas respectés, vous pouvez contacter {{ $platformName }} en priorité. Vous pouvez également saisir l’autorité compétente, notamment la <a href="https://www.cndp.ma/" target="_blank" rel="noopener">CNDP</a> au Maroc. Pour les personnes concernées par l’Union européenne, une autorité de protection des données compétente peut également être saisie.
        </p>

        <h2>15. Mise à jour de la politique</h2>
        <p>
          Cette politique peut être modifiée pour tenir compte de l’évolution de la plateforme, des fonctionnalités IA, des obligations légales ou des recommandations des autorités de protection des données.
        </p>

        <p class="text-sm text-slate-500 dark:text-slate-400">Dernière mise à jour : 05 juin 2026</p>
      </div>
    </section>
  </main>

  @include('layouts.footer')
</body>
</html>
