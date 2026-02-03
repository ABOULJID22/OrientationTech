# Configuration du Thème Filament v4 - Guide Complet

## 📁 Structure des Assets

Votre application a maintenant **deux builds séparés** :

### 1. **Frontend Laravel** (`resources/css/app.css`)
- Utilisé par vos pages publiques (welcome.blade.php, etc.)
- Compilé vers : `public/build/assets/app-*.css`
- Chargé via : `@vite(['resources/css/app.css', 'resources/js/app.js'])`

### 2. **Backend Filament** (`resources/css/filament/admin/theme.css`)
- Utilisé uniquement par le panel admin Filament
- Compilé vers : `public/build/assets/theme-*.css`
- Chargé automatiquement via : `->viteTheme('resources/css/filament/admin/theme.css')`

---

## ✅ Configuration Actuelle

### 1. Fichiers de Configuration

#### `vite.config.js`
```javascript
import { defineConfig } from 'vite';
import laravel, { refreshPaths } from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',           // ← Frontend Laravel
                'resources/js/app.js',
                'resources/css/filament/admin/theme.css',  // ← Backend Filament
            ],           
            refresh: [
                ...refreshPaths,
                'app/Livewire/**',
            ],
        }),
    ],
});
```

#### `postcss.config.js`
```javascript
export default {
    plugins: {
        '@tailwindcss/postcss': {},  // ← Tailwind v4
        autoprefixer: {},
    },
};
```

#### `resources/css/filament/admin/theme.css`
```css
@import '../../../../vendor/filament/filament/resources/css/theme.css';

/* Scanne ces dossiers pour les classes Tailwind utilisées */
@source '../../../../app/Filament/**/*';
@source '../../../../resources/views/filament/**/*';
@source '../../../../app/Livewire/**/*';
@source '../../../../resources/views/livewire/**/*';
```

#### `app/Providers/Filament/AdminPanelProvider.php`
```php
public function panel(Panel $panel): Panel
{
    return $panel
        // ... autres configurations
        ->viteTheme('resources/css/filament/admin/theme.css')  // ← Enregistrement du thème
        // ...
}
```

---

## 🚀 Commandes à Utiliser

### En Développement
```bash
npm run dev
```
- Lance Vite en mode watch
- Recompile automatiquement à chaque changement
- Utilise le serveur HMR (Hot Module Replacement)

### En Production
```bash
npm run build
```
- Compile et minifie tous les assets
- Crée les fichiers dans `public/build/`
- À exécuter avant de déployer

---

## 🎨 Personnalisation du Thème Filament

### Ajouter des Styles Personnalisés
Éditez `resources/css/filament/admin/theme.css` :

```css
@import '../../../../vendor/filament/filament/resources/css/theme.css';

@source '../../../../app/Filament/**/*';
@source '../../../../resources/views/filament/**/*';
@source '../../../../app/Livewire/**/*';
@source '../../../../resources/views/livewire/**/*';

/* Vos styles personnalisés ici */
@layer components {
    .filament-custom-card {
        @apply bg-blue-50 p-4 rounded-lg;
    }
}

/* Ou en CSS pur */
.custom-admin-header {
    background: linear-gradient(to right, #4f6ba3, #3a5a8a);
    padding: 1rem;
}
```

### Utiliser des Classes Tailwind dans Filament
Dans vos fichiers PHP Filament :

```php
// app/Filament/Resources/UserResource.php
use Filament\Forms\Components\TextInput;

TextInput::make('name')
    ->label('Nom')
    ->extraAttributes(['class' => 'font-bold text-blue-600']);
```

Les classes Tailwind seront automatiquement incluses car le dossier `app/Filament/` est scanné par `@source`.

---

## 🔧 Résolution des Problèmes

### Problème : Les styles ne s'appliquent pas

**Solution 1 : Recompiler les assets**
```bash
npm run build
php artisan optimize:clear
```

**Solution 2 : Vider le cache du navigateur**
- `Ctrl + Shift + R` (Windows/Linux)
- `Cmd + Shift + R` (Mac)

### Problème : Icônes Filament manquantes

Les icônes Filament sont incluses automatiquement dans le thème. Si elles ne s'affichent pas :

```bash
php artisan filament:optimize-clear
php artisan optimize:clear
npm run build
```

### Problème : Styles Frontend cassés

Le frontend (pages publiques) utilise `resources/css/app.css`. Vérifiez que :
1. `@vite(['resources/css/app.css', 'resources/js/app.js'])` est présent dans vos layouts
2. Les assets sont compilés : `npm run build`
3. Le fichier `public/build/manifest.json` existe

---

## 📊 Vérification de l'Installation

### 1. Vérifier les fichiers compilés
```bash
ls public/build/assets/
```
Vous devriez voir :
- `app-*.css` (Frontend Laravel)
- `theme-*.css` (Backend Filament)
- `app-*.js`

### 2. Vérifier le manifest
```bash
cat public/build/manifest.json
```

### 3. Tester le panel admin
- Accédez à `/admin`
- Inspectez l'élément `<head>`
- Vérifiez que `theme-*.css` est chargé

### 4. Tester le frontend
- Accédez à `/`
- Inspectez l'élément `<head>`
- Vérifiez que `app-*.css` est chargé

---

## 🎯 Workflow de Développement Recommandé

### 1. **Développement Local**
```bash
# Terminal 1 : Laravel
php artisan serve

# Terminal 2 : Vite
npm run dev
```

### 2. **Modification des Styles Filament**
1. Éditez `resources/css/filament/admin/theme.css`
2. Vite recompile automatiquement (si `npm run dev` est actif)
3. Rafraîchissez le navigateur

### 3. **Modification des Styles Frontend**
1. Éditez `resources/css/app.css`
2. Vite recompile automatiquement
3. Rafraîchissez le navigateur

### 4. **Avant de Commit/Déployer**
```bash
npm run build
php artisan optimize:clear
git add .
git commit -m "Update styles"
```

---

## 📝 Fichiers Modifiés

- ✅ `vite.config.js` - Configuration Vite
- ✅ `postcss.config.js` - Configuration PostCSS pour Tailwind v4
- ✅ `resources/css/app.css` - Frontend Laravel (ordre des @import corrigé)
- ✅ `resources/css/filament/admin/theme.css` - Thème Filament personnalisé
- ✅ `resources/css/filament/admin/tailwind.config.js` - Config Tailwind pour Filament
- ✅ `app/Providers/Filament/AdminPanelProvider.php` - Enregistrement du thème

---

## 🎉 Avantages de cette Configuration

1. **Séparation des Concerns** : Frontend et Backend ont leurs propres styles
2. **Performance** : Chaque partie charge uniquement ses CSS nécessaires
3. **Maintenabilité** : Modifications isolées sans casser l'autre partie
4. **Tailwind v4** : Utilisation de la dernière version avec `@source`
5. **Hot Reload** : Développement rapide avec Vite HMR

---

## 🆘 Support

Si vous rencontrez des problèmes :
1. Arrêtez `npm run dev`
2. Supprimez `public/build/` et `public/hot`
3. Exécutez `npm run build`
4. Exécutez `php artisan optimize:clear`
5. Relancez `npm run dev`

---

**Date de création** : 11 octobre 2025
**Version Filament** : v4.0.12
**Version Tailwind** : v4.1.14
**Version Laravel** : v12.28.1
