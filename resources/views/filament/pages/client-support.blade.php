<x-filament::page>
    <style>
        /* 🌓 Variables pour Light Mode */
        :root {
            --primary-color: #4f6ba3;
            --primary-hover: #3f5888;
            --primary-light: #8aaed0;
            --accent-soft: #6e94c3;
            --accent-muted: #5b7db5;
            --success-color: #10b981;
            --success-bg: #d1fae5;
            --error-color: #ef4444;
            --error-bg: #fee2e2;
            --text-primary: #111827;
            --text-secondary: #6b7280;
            --border-color: #e5e7eb;
            --bg-card: #ffffff;
            --bg-page: #f9fafb;
            --input-readonly-bg: #f3f4f6;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --shadow-hover: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        /* 🌙 Variables pour Dark Mode */
        .dark {
            --primary-color: #8aaed0;
            --primary-hover: #6e94c3;
            --primary-light: rgba(91, 125, 181, 0.28);
            --accent-soft: rgba(110, 148, 195, 0.45);
            --accent-muted: rgba(79, 107, 163, 0.65);
            --success-color: #10b981;
            --success-bg: rgba(16, 185, 129, 0.15);
            --error-color: #f87171;
            --error-bg: rgba(248, 113, 113, 0.15);
            --text-primary: #f3f4f6;
            --text-secondary: #9ca3af;
            --border-color: #374151;
            --bg-card: #1f2937;
            --bg-page: #111827;
            --input-readonly-bg: #374151;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.3);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.4);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.5);
            --shadow-hover: 0 20px 25px -5px rgba(0, 0, 0, 0.6);
        }

        /* 🎨 Container principal */
        .support-container {
            max-width: 1024px;
            margin: 2rem auto;
            padding: 0.75rem;
            transition: background-color 0.3s ease;
        }

        .support-card {
            background: var(--bg-card);
            border-radius: 20px;
            box-shadow: var(--shadow-md);
            overflow: hidden;
            position: relative;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .support-card::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(110, 148, 195, 0.12) 0%, rgba(91, 125, 181, 0.1) 100%);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.4s ease;
        }

        .support-card:hover {
            box-shadow: var(--shadow-hover);
            transform: translateY(-4px);
        }

        .support-card:hover::after {
            opacity: 0.4;
        }

        /* 🎯 Header de la carte */
        .card-header {
            background: linear-gradient(150deg, var(--primary-hover) 0%, var(--primary-color) 55%, var(--accent-muted) 100%);
            padding: 2.4rem 2.25rem;
            position: relative;
            overflow: hidden;
            transition: background 0.3s ease;
        }

        .card-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 290px;
            height: 290px;
            background: rgba(255, 255, 255, 0.14);
            border-radius: 50%;
            transform: translate(30%, -30%);
        }

        /* Cercle décoratif en dark mode */
        .dark .card-header::before {
            background: rgba(255, 255, 255, 0.05);
        }

        .header-content {
            position: relative;
            z-index: 1;
            max-width: 660px;
            display: grid;
            gap: 1rem;
        }

        .accent-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.4rem 0.85rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.18);
            color: #fff;
            font-size: 0.72rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            font-weight: 600;
            width: fit-content;
        }

        .page-title {
            color: #ffffff;
            font-size: clamp(1.65rem, 2vw + 1rem, 1.9rem);
            font-weight: 700;
            margin: 0;
            letter-spacing: -0.025em;
        }

        .page-subtitle {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.95rem;
            margin: 0;
            font-weight: 400;
            line-height: 1.6;
        }

        .header-highlights {
            display: flex;
            flex-wrap: wrap;
            gap: 0.6rem;
        }

        .highlight-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.5rem 1rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.18);
            color: #fff;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .highlight-pill strong {
            font-weight: 700;
        }

        .header-actions {
            position: absolute;
            top: 2.5rem;
            right: 2rem;
            z-index: 2;
        }

        /* 📋 Corps du formulaire */
        .card-body {
            padding: 2.25rem 2.25rem;
            position: relative;
        }

        .support-layout {
            display: grid;
            gap: 2.25rem;
        }

        @media (min-width: 1024px) {
            .support-layout {
                grid-template-columns: minmax(0, 1.5fr) minmax(0, 1fr);
                align-items: start;
            }
        }

        .surface {
            background: var(--bg-card);
            border: 1px solid rgba(79, 107, 163, 0.08);
            border-radius: 16px;
            box-shadow: var(--shadow-sm);
            padding: 1.75rem;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .surface::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            border: 1px solid rgba(138, 174, 208, 0.22);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }

        .surface:hover::after {
            opacity: 1;
        }

        .form-section {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .form-section form {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            flex: 1;
        }

        .info-panel {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .info-card {
            display: grid;
            gap: 1rem;
        }

        .info-card h2 {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .info-card p {
            margin: 0;
            font-size: 0.9rem;
            color: var(--text-secondary);
            line-height: 1.6;
        }

        .info-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 0.6rem;
        }

        .info-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1rem;
            border-radius: 14px;
            background: rgba(138, 174, 208, 0.22);
            color: var(--primary-hover);
            font-size: 0.85rem;
            font-weight: 600;
        }

        .info-list {
            display: grid;
            gap: 1.1rem;
        }

        .info-item {
            display: flex;
            gap: 0.8rem;
            align-items: flex-start;
        }

        .info-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(91, 125, 181, 0.16);
            color: var(--primary-hover);
            flex-shrink: 0;
        }

        .info-item div strong {
            display: block;
            font-size: 0.9rem;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }

        .info-item div span {
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        /* 🚨 Alertes modernisées */
        .alert {
            padding: 1rem 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            border-left: 4px solid;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            animation: slideIn 0.3s ease-out;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert.error {
            background: var(--error-bg);
            color: var(--error-color);
            border-left-color: var(--error-color);
        }

        .alert.success {
            background: var(--success-bg);
            color: var(--success-color);
            border-left-color: var(--success-color);
        }

        .alert ul {
            margin: 0;
            padding-left: 1.25rem;
            list-style: none;
        }

        .alert li {
            position: relative;
            padding-left: 1.5rem;
            margin-bottom: 0.25rem;
        }

        .alert li::before {
            content: '•';
            position: absolute;
            left: 0;
            font-weight: bold;
        }

        /* 📝 Groupes de formulaire */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.625rem;
            font-size: 0.9rem;
            letter-spacing: -0.01em;
            transition: color 0.3s ease;
        }

        .form-group label::after {
            content: '';
            display: block;
            width: 30px;
            height: 2px;
            background: var(--primary-color);
            margin-top: 0.375rem;
            border-radius: 2px;
            transition: width 0.3s ease, background-color 0.3s ease;
        }

        .form-group:focus-within label::after {
            width: 50px;
        }

        /* 🔲 Inputs améliorés */
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1.5px solid var(--border-color);
            border-radius: 12px;
            font-size: 0.9rem;
            color: var(--text-primary);
            background-color: var(--bg-card);
            outline: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-family: inherit;
        }

        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: var(--text-secondary);
            opacity: 0.7;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(110, 148, 195, 0.25);
            transform: translateY(-1px);
        }

        .form-group input[readonly] {
            background-color: var(--input-readonly-bg);
            color: var(--text-secondary);
            cursor: not-allowed;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 130px;
            line-height: 1.6;
        }

        /* 📱 Layout responsive */
        .form-row {
            display: grid;
            gap: 1.5rem;
        }

        @media (min-width: 768px) {
            .form-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* 🔘 Boutons modernes */
        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: auto;
            padding-top: 1.25rem;
            border-top: 1px solid var(--border-color);
            transition: border-color 0.3s ease;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.8rem 1.6rem;
            font-weight: 600;
            font-size: 0.9rem;
            border-radius: 12px;
            cursor: pointer;
            border: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            position: relative;
            overflow: hidden;
        }
        .btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
            box-shadow: var(--shadow-md);
        }

        .btn-primary:hover:not(:disabled) {
            background: var(--primary-hover);
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
        }

        .btn-outline {
            background: transparent;
            color: var(--primary-color);
            border: 2px solid rgba(110, 148, 195, 0.4);
            box-shadow: none;
        }

        .dark .btn-outline {
            color: #60a5fa;
            border-color: rgba(96, 165, 250, 0.4);
        }

        .btn-outline:hover:not(:disabled) {
            background: rgba(110, 148, 195, 0.2);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: white;
            color: var(--text-primary);
            border: 2px solid var(--border-color);
            box-shadow: var(--shadow-sm);
        }

        /* Bouton secondaire en dark mode */
        .dark .btn-secondary {
            background: var(--bg-card);
            border-color: var(--border-color);
        }

        .btn-secondary:hover:not(:disabled) {
            background: #f9fafb;
            border-color: var(--text-secondary);
            transform: translateY(-2px);
        }

        .dark .btn-secondary:hover:not(:disabled) {
            background: #374151;
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn-content {
            position: relative;
            z-index: 1;
        }

        /* Bouton header spécial */
        .btn-header {
            background: rgba(255, 255, 255, 0.95);
            color: var(--primary-color);
            border: none;
        }

        .dark .btn-header {
            background: rgba(31, 41, 55, 0.9);
            color: #60a5fa;
        }

        .btn-header:hover:not(:disabled) {
            background: rgba(255, 255, 255, 1);
            transform: translateY(-2px);
        }

        .dark .btn-header:hover:not(:disabled) {
            background: rgba(55, 65, 81, 1);
        }

        /* ⚡ Spinner de chargement */
        .spinner {
            border: 2.5px solid rgba(255, 255, 255, 0.3);
            border-top: 2.5px solid white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            animation: spin 0.7s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* 📱 Responsive mobile */
        @media (max-width: 900px) {
            .support-container {
                padding: 0.5rem;
                margin: 1.25rem auto;
            }

            .card-header {
                padding: 2rem 1.6rem;
            }

            .header-actions {
                position: static;
                margin-top: 1.5rem;
                width: 100%;
                display: flex;
                justify-content: flex-start;
            }

            .header-highlights {
                gap: 0.5rem;
            }

            .page-title {
                font-size: 1.5rem;
            }

            .card-body {
                padding: 1.75rem 1.5rem;
            }

            .surface {
                padding: 1.5rem;
            }
        }

        @media (max-width: 640px) {
            .support-container {
                margin: 1rem;
            }

            .card-body {
                padding: 1.5rem 1.25rem;
            }

            .form-actions {
                flex-direction: column-reverse;
            }

            .btn {
                width: 100%;
            }

            .info-badges {
                flex-direction: column;
                align-items: flex-start;
            }

            .header-highlights {
                flex-direction: column;
                align-items: flex-start;
            }

            .accent-badge {
                font-size: 0.68rem;
            }
        }

        /* 🎭 Animations d'entrée */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .support-card {
            animation: fadeInUp 0.6s ease-out;
        }
    </style>

    <div class="support-container">
        <div class="support-card">
            <!-- Header -->
            <div class="card-header">
                <div class="header-content">
                    <span class="accent-badge">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2l3 7h7l-5.5 4.5L18 22l-6-4-6 4 1.5-8.5L2 9h7z"></path>
                        </svg>
                        {{ __('filament.pages.client_support.badge_label') }}
                    </span>
                    <h1 class="page-title">{{ __('filament.pages.client_support.heading') }}</h1>
                    <p class="page-subtitle">{{ __('filament.pages.client_support.help_text') }}</p>
                    <div class="header-highlights">
                        <span class="highlight-pill">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                            </svg>
                            <strong>{{ __('filament.pages.client_support.highlight.response_time_label') }}</strong>
                            {{ __('filament.pages.client_support.highlight.response_time_text') }}
                        </span>
                        <span class="highlight-pill">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            {{ __('filament.pages.client_support.highlight.realtime_text') }}
                        </span>
                    </div>
                </div>
                <div class="header-actions">
                    <a href="/admin/support-conversations" class="btn btn-header">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                        <span class="btn-content">{{ __('support.conversations') }}</span>
                    </a>
                </div>
            </div>

            <!-- Body -->
            <div class="card-body">
                <div class="support-layout">
                    <div class="surface form-section">
                        {{-- Alertes --}}
                        @if ($errors->any())
                            <div class="alert error">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0; margin-top: 2px;">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="8" x2="12" y2="12"></line>
                                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                </svg>
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if (session('status'))
                            <div class="alert success">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                </svg>
                                <span>{{ session('status') }}</span>
                            </div>
                        @endif

                        <!-- Formulaire -->
                        <form x-data="{ loading: false }" x-on:submit="loading = true" method="POST" action="{{ route('client.support.submit') }}">
                            @csrf

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="name">{{ __('filament.pages.client_support.name') }}</label>
                                    <input type="text" id="name" name="name" value="{{ old('name', auth()->user()->name) }}" readonly>
                                </div>

                                <div class="form-group">
                                    <label for="email">{{ __('filament.pages.client_support.email') }}</label>
                                    <input type="email" id="email" name="email" value="{{ old('email', auth()->user()->email) }}" readonly>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="phone">{{ __('filament.pages.client_support.phone') }}</label>
                                <input type="text" id="phone" name="phone" value="{{ old('phone', auth()->user()->phone) }}" placeholder="06 12 34 56 78">
                            </div>

                            <div class="form-group">
                                <label for="message">{{ __('filament.pages.client_support.message') }}</label>
                                <textarea id="message" name="message" rows="6" placeholder="{{ __('filament.pages.client_support.placeholder') }}" required>{{ old('message') }}</textarea>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary" x-bind:disabled="loading">
                                    <template x-if="loading">
                                        <span class="spinner"></span>
                                    </template>
                                    <span class="btn-content" x-show="!loading">
                                        {{ __('filament.pages.client_support.send') }}
                                    </span>
                                    <span class="btn-content" x-show="loading" x-cloak>{{ __('filament.pages.client_support.sending') }}</span>
                                </button>
                            </div>
                        </form>
                    </div>

                    <aside class="surface info-panel">
                        <div class="info-card">
                            <h2>{{ __('filament.pages.client_support.info.title') }}</h2>
                            <p>{{ __('filament.pages.client_support.info.description') }}</p>
                            <div class="info-badges">
                                <span class="info-badge">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                    {{ __('filament.pages.client_support.info.badge_personal') }}
                                </span>
                                <span class="info-badge">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 1v22"></path>
                                        <path d="M5 5h14v14H5z"></path>
                                    </svg>
                                    {{ __('filament.pages.client_support.info.badge_history') }}
                                </span>
                            </div>
                        </div>

                        <div class="info-list">
                            <div class="info-item">
                                <span class="info-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M22 2H2v20l4-4h16z"></path>
                                    </svg>
                                </span>
                                <div>
                                    <strong>{{ __('filament.pages.client_support.info.item_conversations_title') }}</strong>
                                    <span>{{ __('filament.pages.client_support.info.item_conversations_text') }}</span>
                                </div>
                            </div>
                            <div class="info-item">
                                <span class="info-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M4 4h16v12H5.17L4 17.17z"></path>
                                        <line x1="12" y1="11" x2="12" y2="11"></line>
                                        <line x1="9" y1="11" x2="9" y2="11"></line>
                                        <line x1="15" y1="11" x2="15" y2="11"></line>
                                    </svg>
                                </span>
                                <div>
                                    <strong>{{ __('filament.pages.client_support.info.item_advice_title') }}</strong>
                                    <span>{{ __('filament.pages.client_support.info.item_advice_text') }}</span>
                                </div>
                            </div>
                        </div>

                        <a href="/admin/support-conversations" class="btn btn-outline">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                            </svg>
                            <span class="btn-content">{{ __('filament.pages.client_support.cta_resume') }}</span>
                        </a>
                    </aside>
                </div>
            </div>
        </div>
    </div>
</x-filament::page>
