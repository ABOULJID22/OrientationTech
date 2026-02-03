<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page expirée</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            color-scheme: light dark;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f9fafb;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: #1f2937;
        }

        .card {
            max-width: 480px;
            width: 100%;
            padding: 2.5rem;
            border-radius: 1.25rem;
            background: #ffffff;
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, .20);
            text-align: center;
        }

        h1 {
            margin: 0 0 1rem;
            font-size: 2.25rem;
            font-weight: 600;
            letter-spacing: -0.02em;
        }

        p {
            margin: 0 0 1.75rem;
            font-size: 1rem;
            line-height: 1.6;
            color: #4b5563;
        }

        a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 9999px;
            background: #2563eb;
            color: #ffffff;
            text-decoration: none;
            font-weight: 600;
            transition: transform .15s ease, box-shadow .15s ease;
            box-shadow: 0 10px 25px rgba(37, 99, 235, .25);
        }

        a:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 30px rgba(37, 99, 235, .35);
        }

        .code-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            padding: 0.4rem 0.9rem;
            border-radius: 9999px;
            background: rgba(37, 99, 235, .12);
            color: #2563eb;
            font-weight: 600;
            letter-spacing: 0.08em;
            font-size: 0.75rem;
        }

        @media (max-width: 640px) {
            body {
                padding: 1.5rem;
            }

            .card {
                padding: 2rem;
            }

            h1 {
                font-size: 1.875rem;
            }
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="code-badge">Erreur 419</div>
        <h1>Page expirée</h1>
        <p style="margin-top: -0.5rem; margin-bottom: 1.5rem; font-size: 0.95rem; color: #6b7280; letter-spacing: .08em; text-transform: uppercase;">
            Page Expired
        </p>
        <p>
            Votre session a expiré ou la page a été rechargée après expiration du jeton de sécurité.
            Veuillez réessayer l'action demandée.
        </p>
        <a href="{{ url('/') }}">Retour à l'accueil</a>
    </div>
</body>
</html>
