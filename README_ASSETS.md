# 📚 Documentation Filament v4 + Vite - Offitrade2

## 🎯 Vue d'Ensemble

Votre application Laravel utilise **deux systèmes de styles séparés** :
- **Frontend Laravel** : Pages publiques (welcome, landing, etc.)
- **Backend Filament** : Panel admin à `/admin`

## 📖 Fichiers de Documentation

| Fichier | Description | Quand l'utiliser |
|---------|-------------|------------------|
| **GUIDE_RAPIDE_ASSETS.md** | Guide rapide d'utilisation quotidienne | ⭐ **Lisez-moi EN PREMIER** |
| **FIX_FRONTEND_STYLES.md** | Résolution: HTML sans CSS | Quand le frontend n'a pas de styles |
| **CONFIGURATION_COMPLETE.md** | Configuration détaillée et personnalisation | Pour personnaliser le thème Filament |
| **FILAMENT_THEME_SETUP.md** | Guide complet de setup initial | Référence technique complète |

## 🚀 Démarrage Rapide

### Installation Initiale
```bash
composer install
npm install
npm run build
php artisan optimize:clear
```

### Usage Quotidien

**Mode Développement :**
```bash
# Terminal 1
php artisan serve

# Terminal 2
npm run dev
```

**Mode Production (test/déploiement) :**
```bash
npm run build
Remove-Item public\hot -Force
php artisan serve
```

## 🛠️ Scripts Utiles

### Vérifier la Configuration
```bash
.\check-filament-setup.ps1
```

### Corriger les Styles Manquants
```bash
.\fix-frontend-styles.ps1
```

## ❌ Problèmes Courants

### 1. Frontend sans styles (HTML uniquement)

**Symptôme :** Pages publiques affichent du HTML brut sans design

**Solution :**
```bash
.\fix-frontend-styles.ps1
# Puis Ctrl+Shift+R dans le navigateur
```

**Cause :** Fichier `public/hot` orphelin après arrêt de Vite

---

### 2. Modifications CSS non visibles

**En mode DEV :**
- Vérifiez que `npm run dev` est actif
- Vérifiez la console Vite pour les erreurs

**En mode PROD :**
```bash
npm run build
Remove-Item public\hot -Force
php artisan config:clear
```

---

### 3. Icônes Filament manquantes

```bash
php artisan filament:optimize-clear
npm run build
# Ctrl+Shift+R dans le navigateur
```

---

### 4. Erreur "ERR_CONNECTION_REFUSED"

**Cause :** `public/hot` existe mais Vite n'est pas actif

**Solution :**
```bash
Remove-Item public\hot -Force
php artisan config:clear
```

---

## 📁 Structure des Fichiers

```
resources/
├── css/
│   ├── app.css                          ← Frontend Laravel
│   └── filament/
│       └── admin/
│           └── theme.css                ← Backend Filament ⭐
├── js/
│   └── app.js
└── views/
    ├── layouts/
    │   └── app.blade.php               ← Charge app.css
    └── filament/                       ← Charge theme.css auto

public/
├── build/                              ← Assets compilés
│   ├── manifest.json                   ← Table de correspondance
│   └── assets/
│       ├── app-*.css                   ← Frontend compilé
│       ├── theme-*.css                 ← Filament compilé
│       └── app-*.js
└── hot                                 ← ⚠️ À supprimer après dev

app/
└── Providers/
    └── Filament/
        └── AdminPanelProvider.php      ← Configure le thème
```

## ⚙️ Configuration

### Vite (`vite.config.js`)
```javascript
input: [
    'resources/css/app.css',           // Frontend
    'resources/js/app.js',
    'resources/css/filament/admin/theme.css',  // Filament
]
```

### PostCSS (`postcss.config.js`)
```javascript
plugins: {
    '@tailwindcss/postcss': {},  // Tailwind v4
    autoprefixer: {},
}
```

### Filament Provider
```php
->viteTheme('resources/css/filament/admin/theme.css')
```

## 🎨 Personnalisation

### Modifier les Styles Frontend
Éditez `resources/css/app.css` :
```css
@import url('https://fonts.bunny.net/css?family=inter:400,700');

@tailwind base;
@tailwind components;
@tailwind utilities;

/* Vos styles personnalisés */
.custom-button {
    @apply bg-blue-500 text-white px-4 py-2 rounded;
}
```

### Modifier le Thème Filament
Éditez `resources/css/filament/admin/theme.css` :
```css
@import '../../../../vendor/filament/filament/resources/css/theme.css';

@source '../../../../app/Filament/**/*';
@source '../../../../resources/views/filament/**/*';

/* Vos styles admin personnalisés */
@layer components {
    .admin-card {
        @apply bg-blue-50 p-6 rounded-lg;
    }
}
```

Puis recompiler :
```bash
npm run build
```

## 🔄 Workflow Git

### Avant de Commit
```bash
# 1. Arrêter npm run dev (Ctrl+C)

# 2. Nettoyer et compiler
Remove-Item public\hot -Force
npm run build

# 3. Commit
git add .
git commit -m "Votre message"
git push
```

### Après Pull
```bash
git pull
composer install
npm install
npm run build
php artisan migrate
php artisan optimize:clear
```

## 📊 Commandes Référence

| Commande | Description | Quand |
|----------|-------------|-------|
| `npm run dev` | Mode développement (Hot Reload) | Pendant le dev |
| `npm run build` | Compiler pour production | Avant commit/déploiement |
| `Remove-Item public\hot -Force` | Supprimer fichier hot | Après arrêt de Vite |
| `php artisan optimize:clear` | Vider tous les caches | Après changements config |
| `php artisan filament:optimize-clear` | Vider cache Filament | Problèmes d'icônes |
| `.\fix-frontend-styles.ps1` | Fix automatique styles | Frontend sans CSS |
| `.\check-filament-setup.ps1` | Vérifier configuration | Diagnostic |

## 🎓 Ressources

### Documentation Officielle
- [Filament v4 Docs](https://filamentphp.com/docs)
- [Tailwind CSS v4](https://tailwindcss.com/docs)
- [Laravel Vite](https://laravel.com/docs/vite)

### Votre Configuration
- **Laravel** : v12.28.1
- **Filament** : v4.0.12
- **Tailwind CSS** : v4.1.14
- **Vite** : v7.1.5

## 💡 Conseils

### ✅ Bonnes Pratiques
1. Toujours supprimer `public/hot` après arrêt de Vite
2. Compiler avec `npm run build` avant de commit
3. Utiliser `Ctrl+Shift+R` pour forcer le refresh navigateur
4. Vérifier la console navigateur (F12) en cas de problème

### ❌ À Éviter
1. Commit `public/hot` dans Git
2. Arrêter Vite sans supprimer `public/hot`
3. Modifier directement les fichiers dans `public/build/`
4. Oublier de compiler avant de déployer

## 🆘 Support

### Problème Non Résolu ?

1. **Exécutez les diagnostics :**
```bash
.\check-filament-setup.ps1
```

2. **Nettoyage complet :**
```bash
Remove-Item public\hot -Force -ErrorAction SilentlyContinue
Remove-Item -Recurse public\build -ErrorAction SilentlyContinue
npm run build
php artisan optimize:clear
php artisan filament:optimize-clear
```

3. **Vérifiez les logs :**
- Console navigateur (F12)
- `storage/logs/laravel.log`
- Terminal Vite pour les erreurs de compilation

---

## ✅ Statut de la Configuration

- ✅ Thème Filament v4 créé et configuré
- ✅ Frontend Laravel séparé
- ✅ Tailwind CSS v4 avec @source
- ✅ PostCSS configuré pour Tailwind v4
- ✅ Vite Hot Module Replacement fonctionnel
- ✅ Build production optimisé
- ✅ Scripts de diagnostic et fix créés

---

**Date de configuration :** 11 octobre 2025  
**Développeur :** ABOULJID22  
**Projet :** Offitrade2  
**Repository :** officielle

---

## 📝 Notes de Version

### v1.0 - 11 octobre 2025
- ✅ Configuration initiale Filament v4 + Vite
- ✅ Résolution problème `public/hot` orphelin
- ✅ Création des scripts de diagnostic et fix
- ✅ Documentation complète en français
