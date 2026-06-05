@php
    $globalSummary = $report['global_summary'] ?? [];
    $pedagogical = $report['pedagogical_analysis'] ?? [];
    $personalityAnalysis = $report['personality_analysis'] ?? [];
    $ikigai = $report['ikigai_analysis'] ?? [];
    $futureDomains = $report['future_domains_analysis'] ?? [];
    $filieres = $report['compatibility_filiales'] ?? [];
    $careers = $report['compatible_careers'] ?? [];
    $educationPaths = $report['education_paths'] ?? [];
    $orientationRec = $report['orientation_recommended'] ?? [];
    $success = $report['success_predictions'] ?? [];
    $actionPlan = $report['action_plan'] ?? [];
    $motivationMsg = $report['motivation_message'] ?? '';
@endphp

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Rapport OrientationTech</title>
    <style>
        @page { margin: 28px 34px 42px; }
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 11px; line-height: 1.45; }
        h1, h2, h3, p { margin: 0; }
        .header { border-bottom: 3px solid #2563eb; padding-bottom: 16px; margin-bottom: 18px; }
        .brand { color: #2563eb; font-size: 24px; font-weight: 800; }
        .brand-subtitle { color: #64748b; font-size: 10px; margin-top: 2px; }
        .student { margin-top: 14px; }
        .student h1 { font-size: 20px; color: #111827; margin-bottom: 4px; }
        .meta { color: #64748b; font-size: 10px; }
        .score { position: absolute; right: 34px; top: 42px; width: 88px; height: 88px; border-radius: 50%; background: #2563eb; color: #fff; text-align: center; padding-top: 18px; box-sizing: border-box; }
        .score strong { display: block; font-size: 22px; }
        .score span { font-size: 8px; text-transform: uppercase; }
        .section { page-break-inside: avoid; margin-bottom: 15px; }
        .section h2 { font-size: 14px; color: #1d4ed8; border-left: 4px solid #2563eb; padding-left: 8px; margin-bottom: 8px; }
        .box { border: 1px solid #dbeafe; background: #f8fbff; border-radius: 6px; padding: 10px 12px; margin-bottom: 8px; }
        .grid { width: 100%; border-collapse: collapse; margin-top: 6px; }
        .grid th { background: #2563eb; color: #fff; text-align: left; padding: 7px; font-size: 10px; }
        .grid td { border-bottom: 1px solid #e5e7eb; padding: 7px; vertical-align: top; }
        .kpi-table { width: 100%; border-collapse: separate; border-spacing: 6px 0; margin-bottom: 10px; }
        .kpi { width: 33%; background: #f1f5f9; border-left: 4px solid #2563eb; padding: 9px; }
        .kpi-label { color: #64748b; font-size: 8px; text-transform: uppercase; font-weight: 700; }
        .kpi-value { font-size: 12px; font-weight: 800; color: #111827; margin-top: 4px; }
        ul { margin: 5px 0 0 16px; padding: 0; }
        li { margin-bottom: 3px; }
        .signature { margin-top: 24px; page-break-inside: avoid; border: 1px solid #bfdbfe; background: #eff6ff; padding: 14px 16px; }
        .signature-line { margin-top: 18px; width: 210px; border-top: 1px solid #2563eb; padding-top: 6px; color: #1e40af; font-weight: 800; }
        .footer { position: fixed; bottom: -24px; left: 0; right: 0; color: #94a3b8; font-size: 8px; text-align: center; border-top: 1px solid #e5e7eb; padding-top: 6px; }
    </style>
</head>
<body>
    <div class="footer">
        OrientationTech - Rapport genere automatiquement a partir du diagnostic, du test personnalise et de l'analyse IA.
    </div>

    <div class="header">
        <div class="brand">OrientationTech</div>
        <div class="brand-subtitle">Conseiller d'orientation intelligent pour les eleves marocains</div>
        <div class="student">
            <h1>Rapport d'orientation complet</h1>
            <p class="meta">
                Eleve: {{ $user->name ?? 'Non renseigne' }} |
                Date: {{ $generatedAt->format('d/m/Y') }} |
                Niveau: {{ $diagnostic->academic_level ?? 'Non renseigne' }}
            </p>
        </div>
        <!-- <div class="score">
            <strong>{{ $globalSummary['global_score'] ?? 0 }}/100</strong>
            <span>Score global</span>
        </div> -->
    </div>

    <table class="kpi-table">
        <tr>
            <td class="kpi"><div class="kpi-label">Profil dominant</div><div class="kpi-value">{{ $globalSummary['dominant_profile'] ?? 'A determiner' }}</div></td>
            <td class="kpi"><div class="kpi-label">Potentiel global</div><div class="kpi-value">{{ $globalSummary['overall_potential'] ?? 'A developper' }}</div></td>
            <td class="kpi"><div class="kpi-label">Confiance</div><div class="kpi-value">{{ $success['confidence_level'] ?? 'Moyenne' }}</div></td>
        </tr>
    </table>

    <div class="section">
        <h2>1. Resume du profil</h2>
        <div class="box">{{ $globalSummary['summary_text'] ?? 'Le rapport sera complete lorsque les resultats seront disponibles.' }}</div>
        @if(!empty($globalSummary['main_strengths']))
            <strong>Forces principales</strong>
            <ul>
                @foreach($globalSummary['main_strengths'] as $strength)
                    <li>{{ $strength }}</li>
                @endforeach
            </ul>
        @endif
        @if(!empty($globalSummary['improvement_axes']))
            <strong>Points a ameliorer</strong>
            <ul>
                @foreach($globalSummary['improvement_axes'] as $axis)
                    <li>{{ $axis }}</li>
                @endforeach
            </ul>
        @endif
    </div>

    <div class="section">
        <h2>2. Analyse intelligente IA</h2>
        <div class="box"><strong>Pour l'eleve:</strong> {{ $pedagogical['student_explanation'] ?? 'Analyse personnalisee non disponible.' }}</div>
        <div class="box"><strong>Pour les parents et enseignants:</strong> {{ $pedagogical['parents_explanation'] ?? $pedagogical['teachers_explanation'] ?? 'Lecture pedagogique non disponible.' }}</div>
    </div>

    <div class="section">
        <h2>3. Personnalite et Ikigai</h2>
        <table class="grid">
            <tr><th>Element</th><th>Resultat</th></tr>
            <tr><td>Type de personnalite</td><td>{{ $personalityAnalysis['dominant_type'] ?? $globalSummary['dominant_profile'] ?? 'Non determine' }}</td></tr>
            <tr><td>Ikigai</td><td>{{ $ikigai['summary'] ?? $ikigai['ikigai_summary'] ?? 'Synthese Ikigai non disponible.' }}</td></tr>
            <tr><td>Style d'apprentissage</td><td>{{ $personalityAnalysis['learning_style'] ?? 'Apprentissage equilibre' }}</td></tr>
        </table>
    </div>

    <div class="section">
        <h2>4. Domaines recommandes</h2>
        <table class="grid">
            <tr><th>Domaine</th><th>Compatibilite</th><th>Justification</th></tr>
            @forelse(array_slice($futureDomains['recommended_domains'] ?? $filieres ?? [], 0, 6) as $domain)
                <tr>
                    <td>{{ $domain['name'] ?? $domain['filiere'] ?? $domain['domain'] ?? 'Domaine recommande' }}</td>
                    <td>{{ $domain['compatibility'] ?? $domain['score'] ?? 0 }}%</td>
                    <td>{{ $domain['justification'] ?? $domain['reason'] ?? 'Recommandation basee sur les resultats du diagnostic et du test personnalise.' }}</td>
                </tr>
            @empty
                <tr><td colspan="3">Aucun domaine disponible pour le moment.</td></tr>
            @endforelse
        </table>
    </div>

    <div class="section">
        <h2>5. Metiers compatibles</h2>
        <table class="grid">
            <tr><th>Metier</th><th>Domaine</th><th>Compatibilite</th><th>Perspective</th></tr>
            @forelse(array_slice($careers, 0, 8) as $career)
                <tr>
                    <td>{{ $career['career'] ?? $career['metier'] ?? $career['name'] ?? 'Metier recommande' }}</td>
                    <td>{{ $career['domain'] ?? 'Informatique' }}</td>
                    <td>{{ $career['compatibility'] ?? $career['score'] ?? 0 }}%</td>
                    <td>{{ $career['employment_outlook'] ?? $career['perspectives'] ?? "Perspectives favorables avec l'evolution de l'IA." }}</td>
                </tr>
            @empty
                <tr><td colspan="4">Aucun metier disponible pour le moment.</td></tr>
            @endforelse
        </table>
    </div>

    <div class="section">
        <h2>6. Parcours de formation</h2>
        <div class="box">
            <strong>Au Maroc:</strong>
            <ul>
                @foreach(($educationPaths['morocco'] ?? $orientationRec['formation_path'] ?? ['Lycee scientifique ou technique', 'BTS / DUT / Licence', 'Ecole d ingenieurs ou universite']) as $path)
                    <li>{{ is_array($path) ? implode(' - ', $path) : $path }}</li>
                @endforeach
            </ul>
        </div>
        <div class="box">
            <strong>A l'international et en ligne:</strong>
            <ul>
                @foreach(($educationPaths['international'] ?? ['Certifications IA/Data/Cybersecurite', 'Cours en ligne reconnus', 'Projets personnels documentes']) as $path)
                    <li>{{ is_array($path) ? implode(' - ', $path) : $path }}</li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="section">
        <h2>7. Plan d'action personnalise</h2>
        <table class="grid">
            <tr><th>Horizon</th><th>Objectif</th></tr>
            <tr><td>1 an</td><td>{{ $actionPlan['one_year'] ?? $actionPlan['year_1'] ?? 'Renforcer les bases scolaires, realiser 2 projets simples et explorer les domaines recommandes.' }}</td></tr>
            <tr><td>3 ans</td><td>{{ $actionPlan['three_years'] ?? $actionPlan['year_3'] ?? 'Choisir une filiere coherente, developper un portfolio et valider des competences numeriques.' }}</td></tr>
            <tr><td>5 ans</td><td>{{ $actionPlan['five_years'] ?? $actionPlan['year_5'] ?? 'Construire un profil specialise, avec stages, certifications et projets professionnels.' }}</td></tr>
        </table>
    </div>

    <div class="section">
        <h2>8. Message de motivation</h2>
        <div class="box">{{ $motivationMsg ?: "Ce rapport est un point de depart. L'orientation reussie se construit avec curiosite, effort, projets concrets et accompagnement." }}</div>
    </div>

    <div class="signature">
        <strong>Validation OrientationTech</strong>
        <p>Ce document a ete genere par OrientationTech a partir des reponses de l'eleve, de son diagnostic, de son test personnalise et d'une analyse intelligente d'orientation.</p>
        <div class="signature-line">Signature OrientationTech<br>Conseiller d'Orientation - Systeme IA</div>
        <p style="margin-top: 8px; color: #64748b; font-size: 9px;">Genere le {{ $generatedAt->format('d/m/Y a H:i') }}</p>
    </div>
</body>
</html>
